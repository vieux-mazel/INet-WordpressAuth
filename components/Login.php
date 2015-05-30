<?php namespace VM\WPLogin\Components;

use Cms\Classes\ComponentBase;
use Validator;
use ValidationException;
use Auth;
use File;
use VM\WPLogin\Models\Wpid as WPuser;
use RainLab\User\Components\Session;
use RainLab\User\Models\User;
use Redirect;
use Request;
use Cms\Classes\Page;
class Login extends ComponentBase
{

    private $params;
    public function componentDetails()
    {
        return [
            'name'        => 'Login Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function defineProperties()
    {
        return array_merge(parent::defineProperties(), [
            'redirectSignup' => [
                'title'       => 'Redirect to (after signup)',
                'description' => 'Page to redirect after the user signed up',
                'type'        => 'dropdown',
                'default'     => ''
            ],
            'redirectLogin' => [
                'title'       => 'Redirect to (after login)',
                'description' => 'Page to redirect after the user login',
                'type'        => 'dropdown',
                'default'     => ''
            ]
        ]);
    }

    public function getRedirectSignupOptions()
    {
        return [''=>'- none -'] + Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function getRedirectLoginOptions()
    {
        return [''=>'- none -'] + Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    /**
     * Executed when this component is bound to a page or layout.
     */
    public function onRun() {
        //$this->appId = Settings::get('app_id');
        $this->redirectSignup = $this->controller->pageUrl($this->property('redirectSignup'));
        $this->redirectLogin = $this->controller->pageUrl($this->property('redirectLogin'));

        return parent::onRun();
    }
    public function onLoginWithWP(){
        require_once base_path('plugins/vm/wplogin/lib/OC_wordpress.php');
        require_once base_path('plugins/vm/wplogin/lib/WPPasswordHash.php');
        $data = post();
        $isSignup = false;
        $rules = [];
        // Validation rules
        $rules['login'] = 'required|between:2,64';
        $rules['password'] = 'required|min:2';

        if (!array_key_exists('login', $data)) {
            $data['login'] = post('username', post('email'));
        }
        // Submit validation
        $validation = Validator::make($data, $rules);
        if ($validation->fails()) {
            throw new ValidationException($validation);
        }

        /*
         * Authenticate user && check Password
         */

         // TODO : $wp_user = fetch user's data on wp database (on every login) => better to handle it with a cron ?

         if ($this->checkPassword(array_get($data, 'login'), array_get($data, 'password'))){
             $socialIds = WPuser::where('wp_id', array_get($data, 'login'))->first(); //wp_id is wp user's username
             if(!$socialIds){ // if wp_id is empty (append on first login) - create a new user
                 $user = User::where( 'email', array_get($data, 'login') . '@no-vm.com')->first();

                if(!$user){
                    $isSignup = true; //user for different redirection, will be used to show tuto
     		        $password = uniqid();
     		        $user = Auth::register([
     			        'name' => array_get($data, 'login'),
                        'surname' => array_get($data, 'login'),
     			        'email' => array_get($data, 'login') . '@no-vm.com' ,
     			        'username' => array_get($data, 'login') . '@no-vm.com' ,
     			        'password' => $password,
     			        'password_confirmation' => $password
     		        ], true);
                }
                $socialIds = new WPuser();
                $socialIds->user_id = $user->id;
                $socialIds->wp_id = array_get($data, 'login');
                $socialIds->save();
            } else {
                $user = $socialIds->user;
            }

            Auth::login($user, true);

            if (post('use_redirect', true)) {
                if ($isSignup) {
                    return Redirect::to(post('redirect_signup', Request::fullUrl()));
                } else {
                    return Redirect::to(post('redirect_login', Request::fullUrl()));
                }
            }

             // Fetch User's group
         }
    }

    /**
     * Check password with WP hashing system
     * @param string $login user's login
     * @param string $pass user's pass
     * @return boolean
     */
    public function checkPassword($uid, $password){
        //require base_path('plugins/vm/wplogin/lib/OC_wordpress.php');
        require_once base_path('plugins/vm/wplogin/lib/OC_wordpress.php');
        require_once base_path('plugins/vm/wplogin/lib/WPPasswordHash.php');
        $wp_instance = new OC_wordpress(); // Wordpress database object
        $wp_instance->OC_wordpress();
        $wp_instance->connectdb();
        $this->params = $wp_instance->params;
        $query = 'SELECT user_login,user_pass FROM '. $this->params['wordpress_db_prefix'] .'users WHERE user_login = "' . str_replace('"','""',$uid) . '"';
        $query .= ' AND user_status = 0';
        $result = $wp_instance->db->query($query);
        if ($result && mysqli_num_rows($result)>0) {
            $row = mysqli_fetch_assoc($result);
            $hash = $row['user_pass'];
            $wp_hasher = new WPPasswordHash(8, TRUE);
            $check = $wp_hasher->CheckPassword($password, $hash);
            //Populate groups here
            //$this->populateUserGroups()
            // Set user details
            //$this->setUserInfos($uid);
            return true;
            //return $row['user_login'];
          }
        echo'LOGGG	'.$query;
    	exit;
        return false;
    }

    /**
     * Create and populate user's groups by fetching Wordpress groups
     * @param string $login user's login
     * @return boolean
     */
    public function populateUserGroups($login){
        return true;
    }

}
