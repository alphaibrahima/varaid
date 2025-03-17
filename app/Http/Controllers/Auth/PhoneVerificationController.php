namespace App\Http\Controllers\Auth;

use Twilio\Rest\Client;
use Illuminate\Http\Request;

class PhoneVerificationController extends Controller
{
    public function show(Request $request)
    {
        return $request->user()->hasVerifiedPhone()
            ? redirect()->route('dashboard')
            : view('auth.verify-phone');
    }

    public function sendOTP(Request $request)
    {
        $twilio = new Client(config('services.twilio.sid'), config('services.twilio.token'));
        
        $verification = $twilio->verify->v2->services(config('services.twilio.verify_sid'))
            ->verifications
            ->create($request->user()->phone, "sms");
            
        return back()->with('status', 'Code de vérification envoyé !');
    }

    public function verify(Request $request)
    {
        $request->validate(['code' => 'required|numeric']);
        
        $twilio = new Client(config('services.twilio.sid'), config('services.twilio.token'));
        
        $verification_check = $twilio->verify->v2->services(config('services.twilio.verify_sid'))
            ->verificationChecks
            ->create([
                'to' => $request->user()->phone,
                'code' => $request->code
            ]);
        
        if ($verification_check->status === 'approved') {
            $request->user()->update(['phone_verified' => true]);
            return redirect()->route('dashboard');
        }
        
        return back()->withErrors(['code' => 'Code invalide']);
    }
}