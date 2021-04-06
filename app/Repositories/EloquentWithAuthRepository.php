<?php

namespace App\Repositories;

use App\Repositories\EloquentRepository;

abstract class EloquentWithAuthRepository extends EloquentRepository
{
    /**
     * get model
     * @return string
     */
    abstract public function getModel();

    /**
     * Get All
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getAll($user_id = null)
    {
        return $this->_model->where('user_id', $user_id)->get();
    }

    /**
     * Get one
     * @param $id
     * @return mixed
     */
    public function find($id, $user_id = null)
    {
        $result = $this->_model
            ->where([
                ['id', '=', $id],
                ['user_id', '=', $user_id],
            ])->first();

        return $result;
    }

    /**
     * Update
     * @param $id
     * @param array $attributes
     * @return bool|mixed
     */
    public function update($id, $attributes, $user_id = null)
    {
        $result = $this->find($id, $user_id);

        if ($result) {
            $result->update($attributes);
            return $result;
        }

        return false;
    }

    /**
     * Delete
     *
     * @param $id
     * @return bool
     */
    public function delete($id, $user_id = null)
    {
        $result = $this->find($id, $user_id);

        if ($result) {
            $result->delete();
            return true;
        }

        return false;
    }
}
