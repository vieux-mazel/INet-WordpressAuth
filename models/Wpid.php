<?php namespace VM\WPLogin\Models;

use Model;

/**
 * wpid Model
 */
class Wpid extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'vm_wplogin_wpids';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [];
    public $belongsTo = [
        'user' => ['\RainLab\User\Models\User']
    ];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

}
