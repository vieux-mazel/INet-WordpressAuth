<?php namespace VM\WPLogin;

use System\Classes\PluginBase;

/**
 * WPLogin Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * @var array Plugin dependencies
     */
    public $require = [
        'RainLab.User'
    ];

    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'WPLogin',
            'description' => 'Provide Wordpress auth',
            'author'      => 'VM',
            'icon'        => 'icon-leaf'
        ];
    }


    /**
     * Registers any front-end components implemented in this plugin.
     */
    public function registerComponents()
    {
        return [
            '\VM\WPLogin\Components\Login' => 'WPLogin',
        ];
    }

    /**
     * Boot method, called right before the request route.
     */
    public function boot()
    {
        //FacebookSession::setDefaultApplication(Settings::get('app_id'), Settings::get('app_secret'));

        \RainLab\User\Models\User::extend(function (\RainLab\User\Models\User $model) {
            $model->hasOne['wp_id'] = ['\VM\WPLogin\Models\Wpid'];
        });
    }
// TODO : Créer la page des settings
/*
*    public function registerSettings()
*    {
*        return [
*            'settings' => [
*                'label'       => 'User Wordpress Settings',
*                'description' => 'Login via Wordpress.',
*                'category'    => 'User Wordpress',
*                'icon'        => 'icon-leaf',
*                'class'       => '\VM\WPLogin\Models\Settings',
*                'order'       => 500,
*                'keywords'    => 'facebook user',
*            ]
*        ];
*    }
*/

}
