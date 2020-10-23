<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Checkout;
use App\Models\Account;
use App\Models\Session;
use AfricasTalking\SDK\AfricasTalking;

class FinanceController extends Controller
{

    /**
     * global variables required for our app
     * @var $session_id,
     * @var $text
     * @var $phone_number,
     * @var $service_code
     * @var $level
     * @var $AT
     * @var $AT_username
     * @var $AT_api_key
     */
    private $session_id,$service_code,$phone_number,$text;
    private $AT_username, $AT_api_key;
    private AfricasTalking $AT;

    protected string $screen_response,$header;
    protected $text_array,$user_response;
    protected int $level;

    public function __construct(Request $request){
        //get the POST request from AT
        $this->session_id   =$request->get('sessionId');
        $this->service_code =$request->get('serviceCode');
        $this->phone_number =$request->get('phoneNumber');
        $this->text         =$request->get('text');

        $this->AT_api_key   =env('AT_API_KEY');
        $this->AT_username  =env('AT_USERNAME');
        $this->AT           =new AfricasTalking($this->AT_username,$this->AT_api_key);

        $this->header           ="Content-type:text/plain";
        $this->screen_response  ="";
        //set the default level at 0
        $this->level            =0;
        $this->text_array       =explode("*",$this->text);

        $this->user_response    =trim(end($this->text_array));
    }
    public function ussd()
    {
        //1.Check the level of the user from the DB and retain default level if none is found for this session
        $new_level=Session::where('phone_number',$this->phone_number)->pluck('session_level')->first();
        //dd($new_level);

        if(!empty($new_level)){
            $this->level=$new_level;
        }
        //2.Check if the incoming phone number is registered(kind of login)
        $visiting_user=User::where('phone_number','LIKE','%'.$this->phone_number.'%')->limit(1)->count();
        //dd($visiting_user);

        //3. Check if the user is available (yes)->Serve the menu(login user-finance based system for security); (no)->Register the user
        if($visiting_user>0)
        {
            //let the user login to proceed
            $this->login();
        }
        else
        {
            //register non existing users
            $this->user_registration();
        }

    }
    public function login()
    {
        //5.login the user then update the level
        //dd($this->level);
        switch ($this->level)
        {
            case 0:
                switch ($this->user_response)
                {
                    case "":
                        //display get login credentials
                        $this->login_username();
                        Session::where('phone_number','=',$this->phone_number)->update(['session_level'=>1]);
                        break;
                    default:
                        //sth happens
                }
            case 1:
                $username=strtolower(trim(htmlspecialchars($this->user_response)));
                if(User::where('username','=',$username)->limit(1)->count()){
                    //if username exists
                    $this->PIN();
                    //update the level
                    Session::where('phone_number','=',$this->phone_number)->update(['session_level'=>2]);
                }
                else
                {
                    $this->screen_response="Invalid username,try again\n";
                    //demote the user to level 0
                    Session::where('phone_number','=',$this->phone_number)->update(['session_level'=>0]);
                }
                break;

            case 2:
                $personal_identifier=strtolower(trim(htmlspecialchars($this->screen_response)));
                if(User::where('PIN','=',$personal_identifier)->count()){
                    //if login auth success,display main menu and update level
                    $this->display_NDS_main_menu();
                    Session::where('phone_number','=',$this->phone_number)->update(['session_level'=>3]);
                }
                break;
            default:
                //do sth
        }
    }
    public function user_registration()
    {
        //4.a completely new user visits our ussd.Register the user
        switch ($this->level)
        {
            case 0:
                switch($this->user_response)
                {
                    case "":
                        Session::create([
                            'at_session_id' =>$this->session_id,
                            'phone_number'  =>$this->phone_number,
                            'session_level' =>1
                        ]);
                        User::create([
                            'phone_number'  =>$this->phone_number,
                        ]);
                        Account::create([
                            'phone_number'  =>$this->phone_number,
                        ]);
                        Checkout::create([
                            'phone_number'  =>$this->phone_number
                        ]);
                        //display the registration form
                        $this->new_user_welcome_screen();
                        break;
                    default:
                        //do something here...

                }
            case 1:
                switch ($this->user_response)
                {
                    case "1":
                        //get user credentials
                        $this->firstname();
                        Session::where('phone_number','=',$this->phone_number)->update(['session_level'=>2]);
                        break;
                    case "2":
                        //tell the user about us
                        $this->about_us();
                        break;
                    case "3":
                        //terms and conditions
                        $this->terms_and_conditions();
                        break;
                    case "99":
                        //user exiting the app
                        $this->exit_app();
                        break;
                    default:
                        $this->screen_response="Invalid choice,try again\n";
                        $this->ussd_proceed($this->screen_response);
                        //demote user to level 0
                        Session::where('phone_number','=',$this->phone_number)->update(['session_level'=>0]);
                }
            case 2:
                //this level,the user has provided his/her firstname
                $firstname=trim(htmlspecialchars($this->user_response));
                if((!empty($firstname)) || (!is_numeric($firstname))){
                    //update firstname
                    User::where('phone_number','=',$this->phone_number)->update(['first_name'=>$firstname]);
                    //display the next input,middle name
                    $this->middle_name();
                    //update the level to 3
                    Session::where('phone_number','=',$this->phone_number)->update(['session_level'=>3]);
                }
                else
                {
                    //invalid first name input
                    $this->screen_response="Invalid first name,try again to proceed\n";
                    $this->header;
                    $this->ussd_proceed($this->screen_response);
                    //demote user to level 1
                    Session::where('phone_number','=',$this->phone_number)->update(['session_level'=>1]);
                }
            case 3:
                $middle_name=trim(htmlspecialchars($this->user_response));

                if((!empty($middle_name)) || (!is_numeric($middle_name)))
                {
                    //update lastname
                    User::where('phone_number','=',$this->phone_number)->update(['last_name'=>$middle_name]);
                    //display the next input,username
                    $this->lastname();
                    //update the level to 4
                    Session::where('phone_number','=',$this->phone_number)->update(['session_level'=>4]);
                }
                else
                {
                    //invalid middle name
                    $this->screen_response="Invalid middle name,try again\n";
                    $this->header;
                    $this->ussd_proceed($this->screen_response);
                    //demote user to level 2
                    Session::where('phone_number','=',$this->phone_number)->update(['session_level'=>2]);
                }
            case 4:
                $lastname=trim(htmlspecialchars($this->user_response));

                if((!empty($lastname)) || (!is_numeric($lastname)))
                {
                    //update lastname
                    User::where('phone_number','=',$this->phone_number)->update(['last_name'=>$lastname]);
                    //display the next input,username
                    $this->username();
                    //update the level to 4
                    Session::where('phone_number','=',$this->phone_number)->update(['session_level'=>5]);
                }
                else
                {
                    //invalid last name
                    $this->screen_response="Invalid last name,try again\n";
                    $this->header;
                    $this->ussd_proceed($this->screen_response);
                    //demote user to level 2
                    Session::where('phone_number','=',$this->phone_number)->update(['session_level'=>3]);
                }
            case 5:
                $username=trim(htmlspecialchars($this->user_response));
                if((!empty($username)) || (!is_numeric($username)))
                {
                    //update username
                    User::where('phone_number','=',$this->phone_number)->update(['username'=>$username]);
                    //display the next input,username
                    $this->PIN();
                    //update the level to 6
                    Session::where('phone_number','=',$this->phone_number)->update(['session_level'=>6]);
                }
                else
                {
                    //invalid username
                    $this->screen_response="Invalid username,try again\n";
                    $this->header;
                    $this->ussd_proceed($this->screen_response);
                    //demote user to level 4
                    Session::where('phone_number','=',$this->phone_number)->update(['session_level'=>4]);
                }
            case 6:
                $PIN=trim(htmlspecialchars($this->user_response));
                if(!empty($PIN))
                {
                    //update PIN
                    User::where('phone_number','=',$this->phone_number)->update(['username'=>$PIN]);
                    //display the main menu(demote user to level 2
                    Session::where('phone_number','=',$this->phone_number)->update(['session_level'=>2]);
                }
                else
                {
                    //no pin provided
                    $this->screen_response="Enter your PIN\n";
                    $this->header;
                    $this->ussd_proceed($this->screen_response);
                    //demote user to level 5
                    Session::where('phone_number','=',$this->phone_number)->update(['session_level'=>5]);
                }
            default:
                //do sth here
        }
    }
    public function middle_name(){
        $this->screen_response="Enter your middle name\n";
        $this->ussd_proceed($this->screen_response);
    }
    public function firstname()
    {
        $this->screen_response="Enter your first name\n";
        $this->ussd_proceed($this->screen_response);
    }
    public function lastname()
    {
        $this->screen_response="Enter your last name\n";
        $this->ussd_proceed($this->screen_response);
    }
    public function username()
    {
        $this->screen_response="Enter your username\n";
        $this->ussd_proceed($this->screen_response);
    }
    public function PIN()
    {
        $this->screen_response="Enter your PIN\n";
        $this->ussd_proceed($this->screen_response);
    }
    public function about_us(){
        $this->screen_response="We are a micro finance sacco that operates in Kenya.\n";
        $this->screen_response.="We provide small scale farmers with loans\n";
        $this->screen_response.="For more information,visit www.nds.org\n";

        $this->ussd_finish($this->screen_response);
    }
    public function terms_and_conditions(){
        $this->screen_response="visit www.nds.org/terms-and-conditions for more\n";
        $this->ussd_finish($this->screen_response);
    }
    public function exit_app(){
        $this->screen_response="Thank you for visiting Nyangiela Development Sacco,see you soon!\n";
        $this->ussd_finish($this->screen_response);
    }
    public function new_user_welcome_screen(){
        $this->screen_response="<strong>Welcome to Nyangiela Development Sacco(NDS)</strong>\n";
        $this->screen_response.="1.Register\n";
        $this->screen_response.="2.About Us\n";
        $this->screen_response.="3.Terms and Conditions\n";
        $this->screen_response.="99.EXIT";

        $this->header;
        $this->ussd_proceed($this->screen_response);
    }

    public function display_NDS_main_menu()
    {}
    public function ussd_proceed($proceed)
    {
        echo "CON $proceed";
    }
    public function ussd_finish($finish)
    {
        echo "END $finish";
    }
    public function login_username()
    {
        $this->screen_response="<strong>Login to access your NDS account\n";
        $this->screen_response.="Enter your username\n";
        $this->ussd_proceed($this->screen_response);
    }
    public function account_enquiries(): void
    {}
    public function transfer_funds(): void
    {}
    public function send_to_mpesa(): void
    {}
    public function bill_payments(): void
    {}
    public function airtime_purchase(): void
    {}
    public function loans(): void
    {}
    public function change_pin(): void
    {}
    public function stop_cheque(): void
    {}
    public function delete_account(): void
    {}
    public function electricity_bill(): void
    {}
    public function internet_bill(): void
    {}
    public function water_bill(): void
    {}
    public function dstv_bill(): void
    {}
    public function NHIF_bill(): void
    {}
    public function nairobi_county_payments(): void
    {}

}
