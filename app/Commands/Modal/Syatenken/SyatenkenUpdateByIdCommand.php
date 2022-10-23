<?php

namespace App\Commands\Modal\Syatenken;

use App\Models\Targetcars;
use App\Lib\Util\Constants;
use App\Commands\Command;
use App\Http\Requests\SearchRequest;
use App\Original\Util\CodeUtil;
use Session;

/**
 * 車点検内容更新コマンド
 *
 * @author yhatsutori
 */
class SyatenkenUpdateByIdCommand extends Command
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
        $tgcMObj = Targetcars::findOrFail( $this->id );
        
        // 更新対象のカラムを定義
        $columns = [
            'tgc_status', // 意向結果
            //'tgc_action', // 活動内容
            'tgc_status_update',
            'tgc_memo', // メモ
            'tgc_daigae_car_name' // 代替予定車種
        ];

        // 2020/06/23 add code
        $this->requestObj->tgc_status_update = date('Y-m-d');
        if ($this->requestObj->tgc_status == '') {
            $this->requestObj->tgc_status = NULL;
            $this->requestObj->tgc_status_update = NULL;
        }
        
        /**
         * 更新用に値を詰め込む
         */
        foreach( $columns as $column ){
            $tgcMObj->{$column} = $this->requestObj->{$column};
        }

        if (session(Constants::SEC_PROCESS_SCREEN_ID) == Constants::J00 || session(Constants::SEC_PROCESS_SCREEN_ID) == Constants::R00) {
            $katsudoCsv = CodeUtil::getIkouLatestName($this->requestObj->tgc_status);
            $currentDate = date('Y-m-d');
            if (empty($this->requestObj->tgc_status)) {
                $sqlUpdate = "UPDATE tb_manage_info
                    SET mi_dsya_status_katsudo_csv = NULL
                        ,mi_dsya_status_katsudo_date = NULL
                        ,mi_dsya_status_katsudo_code = NULL
                WHERE mi_inspection_id = '{$tgcMObj->tgc_inspection_id}'
                    AND mi_inspection_ym = '{$tgcMObj->tgc_inspection_ym}'
                    AND mi_car_manage_number = '{$tgcMObj->tgc_car_manage_number}'";
            } else {
                $sqlUpdate = "UPDATE tb_manage_info
                    SET mi_dsya_status_katsudo_csv = '{$katsudoCsv}'
                        ,mi_dsya_status_katsudo_date = '{$currentDate}'
                        ,mi_dsya_status_katsudo_code = '{$this->requestObj->tgc_status}'
                WHERE mi_inspection_id = '{$tgcMObj->tgc_inspection_id}'
                    AND mi_inspection_ym = '{$tgcMObj->tgc_inspection_ym}'
                    AND mi_car_manage_number = '{$tgcMObj->tgc_car_manage_number}'";
            }
            \DB::statement( $sqlUpdate );
        }

        // 更新する
        $tgcMObj->save();

        // 更新のセッションを用意
        Session::put('update', 1);

        return $tgcMObj;
    }
}
