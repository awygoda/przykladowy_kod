<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\FormCooperiation;
use App\UserSoundSetting;
use App\ConfigEmail;
use App\Category;
use App\UserLike;
use App\CmsPage;
use App\Social;
use Session;
use Mail;
use Auth;

class ClientCooperationController extends Controller
{
    public function index()
    {
        $cmsPage = CmsPage::findOrFail(7)->content;
        $socials = Social::all();
        $categories = Category::where('parent', 1)->get();

        if (Auth::user()) {
            $userLikes = UserLike::where('user_add_id', Auth::user()->id)->with('userData')->get();
        } else {
            $userLikes = false;
        }
        
        if (Auth::user()) {
            $userSoundSetting = UserSoundSetting::where('user_id', Auth::user()->id)->first();
        } else {
            $userSoundSetting = false;
        }
        return view('client.wspolpraca', compact('cmsPage', 'socials', 'categories', 'userLikes', 'userSoundSetting'));
    }

    public function store(Request $request)
    {

        $attributes = [
            'email' => 'adres e-mail',
            'content' => 'treść wiadomości',
            'consent_personal_data' => 'zgoda na przetważanie danych osobowych w celu kontaktu we wskazanej sprawie.'
        ];

        $this->validate($request, [
            'email' => 'required|email|max:255',
            'content' => 'required',
            'consent_personal_data' => 'required'
        ], [], $attributes);

        try {
            DB::beginTransaction();
            $topic = "Współpraca";
            $consent_trade_information = 0;
            if (isset($request->consent_trade_information)) {
                $consent_trade_information = 1;
            }
            $consent_personal_data = 0;
            if (isset($request->consent_personal_data)) {
                $consent_personal_data = 1;
            }
            $formCooperation = new FormCooperiation($request->all());
            $formCooperation->topic = $topic;
            $formCooperation->consent_trade_information = $consent_trade_information;
            $formCooperation->consent_personal_data = $consent_personal_data;
            $formCooperation->save();


            $email = ConfigEmail::findOrFail(1)->email;
            $data =  array('emailFrom' => $request->input('email'), 'subject' => $topic, 'emailTo' => $email);
            try {
                Mail::send('client.emails.cooperation', ['content' => $request->input('content')], function ($message) use ($data)
                {
                    $message->from($data['emailFrom']);
                    $message->subject('Temat: ' .$data['subject']);
                    $message->to($data['emailTo']);
                });
            } catch(\Exception $e){
                $errors = 'Wystąpił problem z wysłaniem zapytania. Prosimy spróbować później.';
                return redirect()->back()->withErrors($errors);
            }

            DB::commit();
        } catch(\Exception $e){
            DB::rollback();
            $errors = 'Błąd zapisu do bazy danych';
            return redirect()->back()->withErrors($errors);
        }

        Session::flash('message', 'Dziękujemy za wiadomość! Skontaktujemy się z Tobą tak szybko, jak to będzie możliwe.');
        return redirect('/wspolpraca');
    }
}