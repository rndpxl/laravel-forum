<?php namespace Riari\Forum\Repositories;

use Config;
use Riari\Forum\Models\Thread;

class Threads extends BaseRepository {

    public function __construct(Thread $model)
    {
        $this->model = $model;

        $this->itemsPerPage = Config::get('forum::integration.threads_per_category');
    }

    public function getByID($threadID, $with = array())
    {
        return $this->getFirstBy('id', $threadID, $with);
    }

}
