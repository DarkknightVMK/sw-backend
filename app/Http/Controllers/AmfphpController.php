<?php
namespace App\Http\Controllers;

use Amfphp_Core_HttpRequestGatewayFactory;
use Amfphp_Core_Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Amfphp_Core_Amf_Deserializer;
use Illuminate\Support\Facades\Auth;
use App\Models\sessions;
use App\Models\Avatars;
use App\Models\Users;


class AmfphpController extends Controller {

    public function amfphp(Request $request)
    {
        // from request url get the ?jsessionid=xxxx

        // $request->session = $request->query('jsessionid');
        // get session from request, it's passed in the route
        // $sesh = $request->session;
        $myConfig = new Amfphp_Core_Config();
        $myConfig->serviceFolders = array();
        $myConfig->serviceFolders [] = dirname(__FILE__) . '/../Services/';

        $gateway = Amfphp_Core_HttpRequestGatewayFactory::createGateway($myConfig);

        // login the user via the session
        // strip anything after ; in the session
        $sess = explode(';', $request->session)[0];
        // if sess is empty
        if (empty($sess) || $sess == null) {
            // try to get it from the header
            $sess = $request->header('swsid');
        }
        $session = sessions::where('SWSID', $sess)->first();
        if (!$session) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        // authenticate user
        // set auth user
        Auth::loginUsingId($session->user_id);
        // set session id
        session (['id' => $sess]);
        // set session avatar_id
        session (['avatar' => Users::where('id', $session->user_id)->first()->defaultAvatar]);
        // set session user_id
        session (['user' => $session->user_id]);

        $gateway->service();
        $gateway->output();
        

    }
}
