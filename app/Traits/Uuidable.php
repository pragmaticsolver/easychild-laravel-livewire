<?php

namespace App\Traits;

use Ramsey\Uuid\Uuid;
use Illuminate\Database\Eloquent\ModelNotFoundException;

trait Uuidable
{
    /**
     * Trait boot: if no uuid value exists, create one
     */
    public static function bootUuidable()
    {
        /**
         * Creating the record
         */
        static::creating(function ($obj) {
            if (empty($obj->uuid) || static::findByUuid($obj->uuid)) {
                // Make 100% sure it's unique
                do {
                    $uuid = Uuid::uuid4();
                } while ($obj->findByUuid($uuid->toString()));

                // Set the uuid prior to save
                $obj->attributes['uuid'] = $uuid->toString();
            }
        });
    }

    /**
     * Find a model by its uuid
     *
     * @param $uuid
     * @return mixed
     */
    public static function findByUuid($uuid, $columns = ['*'])
    {
        return static::where('uuid', '=', $uuid)->first($columns);
    }

    /**
     * Find a model by its UUID or throw an exception.
     *
     * @param $uuid
     * @param array $columns
     * @return mixed
     */
    public static function findByUUIDOrFail($uuid, $columns = ['*'])
    {
        $result = static::findByUuid($uuid, $columns);

        if ($result) {
            return $result;
        }

        throw (new ModelNotFoundException)->setModel(get_called_class());
    }
}
