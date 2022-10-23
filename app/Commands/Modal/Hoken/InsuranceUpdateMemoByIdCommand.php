<?php

namespace App\Commands\Modal\Hoken;

use App\Lib\Util\DateUtil;
use App\Original\Codes\Insurance\InsuStatusCodes;
use App\Original\Codes\Insurance\InsuActionCodes;
use App\Original\Util\SessionUtil;
use App\Models\Insurance;
use App\Commands\Command;
use App\Http\Requests\SearchRequest;
use Session;

/**
 * 保険内容更新コマンド
 * 
 * @author Luc Dang
 */
class InsuranceUpdateMemoByIdCommand extends Command
{
    protected $id;
    protected $requestObj;

    /**
     * コンストラクタ
     *
     * @param $id 活動内容のID
     * @param ActionListRequest $requestObj 更新内容
     */
    public function __construct( $id, SearchRequest $requestObj ){
        $this->id = $id;
        $this->requestObj = $requestObj;
    }

    /**
     * メインの処理
     * @return 活動内容
     */
    public function handle(){
        // IDで活動内容を取得する
        $insuMObj = Insurance::findOrFail( $this->id );

        // 更新する値の配列を取得
        $setValues = $this->requestObj->all();

        // ユーザー情報を取得(セッション)
        $loginAccountObj = SessionUtil::getUser();

        // 値が空でない時に処理
        if( !empty( $setValues["insu_alert_memo"] ) == True ){
            if( !empty( $insuMObj->insu_alert_memo ) == True ){
                $insuMObj->insu_alert_memo = $loginAccountObj->getUserName() . "さんからの伝達事項 " . date("Y/m/d") . "\n" . $setValues["insu_alert_memo"] . "||" . $insuMObj->insu_alert_memo;
            }else{
                $insuMObj->insu_alert_memo = $loginAccountObj->getUserName() . "さんからの伝達事項 " . date("Y/m/d") . "\n" . $setValues["insu_alert_memo"];
            }

            // 更新する
            $insuMObj->save();

            // 更新のセッションを用意
            Session::put('update', 1);
        }

        return $insuMObj;
    }
}
