<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Original\Util\SessionUtil;
use Illuminate\Contracts\Auth\Guard;
use Auth;

class MaintenController extends Controller
{

    /**
     * Create a new authentication controller instance.
     *
     * @param  \Illuminate\Contracts\Auth\Guard  $auth
     * @return void
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // メンテナンスチェック
        $mainten_start = config('original.mainten_start');
        try {
            $match_date = date('Y/m/d', strtotime($mainten_start));
            // メンテナンス対象日
            if($mainten_start != "" && $match_date == date("Y/m/d") ){
                // 開始時間
                $star_hour = strtotime(config('original.mainten_start'));
                // 終了時間
                $end_hour = strtotime(config('original.mainten_end'));
                // 設定時間チェック
                if ($star_hour < $end_hour) {
                    // メンテナンス中の条件チェック
                    if ($star_hour <= time() && time() <= $end_hour) {
                        // ユーザー情報を削除(セッション)
                        SessionUtil::removeUser();
                        $this->auth->logout();
                        return view('auth.mainten');
                    }
                }
            }
        } catch ( Exception $ex ) {
            \Log::error('メンテナンス値設定不正'.ex);
        }
        return redirect('auth/login');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
