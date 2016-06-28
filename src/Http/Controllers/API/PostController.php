<?php namespace Riari\Forum\Http\Controllers\API;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Riari\Forum\Models\Post;
use Riari\Forum\Models\Thread;
use App\Models\AmbassadorChecklist;

class PostController extends BaseController
{
	/**
	 * Return the model to use for this controller.
	 *
	 * @return Post
	 */
	protected function model()
	{
		return new Post;
	}

	/**
	 * Return the translation file name to use for this controller.
	 *
	 * @return string
	 */
	protected function translationFile()
	{
		return 'posts';
	}

	public function recentPosts(Request $request) {

		$recentPosts = $this->model()->withRequestScopes($request)->get();

		return $this->response($recentPosts);
	}

	/**
	 * GET: Return an index of posts by thread ID.
	 *
	 * @param  Request  $request
	 * @return JsonResponse|Response
	 */
	public function index(Request $request)
	{
		$this->validate($request, ['thread_id' => ['required']]);

		$posts = $this->model()->where('thread_id', $request->input('thread_id'))->get();

		return $this->response($posts);
	}

	/**
	 * GET: Return a post.
	 *
	 * @param  int  $id
	 * @param  Request  $request
	 * @return JsonResponse|Response
	 */
	public function fetch($id, Request $request)
	{
		$post = $this->model()->find($id);

		if (is_null($post) || !$post->exists) {
			return $this->notFoundResponse();
		}

		return $this->response($post);
	}

	/**
	 * POST: Create a new post.
	 *
	 * @param  Request  $request
	 * @return JsonResponse|Response
	 */
	public function store(Request $request)
	{
		$this->validate($request, ['thread_id' => ['required'], 'author_id' => ['required'], 'content' => ['required']]);

		$thread = Thread::find($request->input('thread_id'));
		$this->authorize('reply', $thread);

		$post = $this->model()->create($request->only(['thread_id', 'post_id', 'author_id', 'content']));
		$post->load('thread');

		// Set ambassador checklist item as completed
		$checklistItem = AmbassadorChecklist::firstOrNew([
			                                                 'user_id' => $post->author_id,
			                                                 'key' => 'forum-posted'
		                                                 ]);

		if (!$checklistItem->is_completed)
		{
			$checklistItem->is_completed = true;
			$checklistItem->save();
		}


		return $this->response($post, $this->trans('created'), 201);
	}
}