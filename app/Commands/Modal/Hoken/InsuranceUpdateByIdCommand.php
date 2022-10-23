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
 * 車点検内容更新コマンド
 * 
 * @author yhatsutori
 */
class InsuranceUpdateByIdCommand extends Command
{
    protected $id;
    protected $requestObj;

    protected $userObj;
    protected $date;
    protected $status;
    protected $action;
    protected $history;
    
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
        
        ####################
        ## 更新履歴を先に取得
        ####################
        
        // 変更前と同じ意向結果または、活動内容でない時に処理
        if( $insuMObj->insu_status != $this->requestObj->insu_status || $insuMObj->insu_action != $this->requestObj->insu_action ){
            // 意向結果を取得
            $status = ( new InsuStatusCodes() )->getValue( $this->requestObj->insu_status );

            // 活動内容を取得
            $action = ( new InsuActionCodes() )->getValue( $this->requestObj->insu_action );

            // 現在の日付を取得
            $date = DateUtil::currentDay();

            // ユーザー情報を取得(セッション)
            $userObj = SessionUtil::getUser();

            // 更新履歴を取得
            $insuMObj->insu_updated_history = $userObj->getUserName() . "   " . $date . "   " . $status . "   " . $action . "\n" . $insuMObj->insu_updated_history;
        }

        // 更新対象のカラムを定義
        $columns = [
            'insu_contact_plan_date', // 接触予定日
            'insu_contact_date', // 接触日
            'insu_status', // 意向結果
            'insu_action', // 活動内容
            'insu_memo', // メモ
            'insu_contact_jigu', // 治具
            'insu_contact_mail_tuika', // メール追加
            'insu_contact_taisyo', // 接触対象
            'insu_contact_taisyo_name', // 接触対象者名
            'insu_contact_syaryo_type', // 車両保険付帯
            'insu_contact_daisya', //代車特約付帯

            'insu_contact_period', // 保険期間（長期の推進）
            'insu_contact_keijyo_ym', // 計上予定月
            'insu_contact_daigae_car_name', // 車両の代替提案
            'insu_kakutoku_company_name', // 獲得保険会社

            //'insu_add_tetsuduki_date', // 手続き完了日
            //'insu_add_tetsuduki_detail', // 手続き内容
            'insu_add_keijyo_date', // 計上処理日
            'insu_add_keijyo_ym', // 計上反映月

            'insu_staff_info_toss', //スタッフからの情報トス
            'insu_toss_staff_name', //情報トススタッフ名
            'insu_pair_fleet' //ペアフリート

        ];
        
        /**
         * 更新用に値を詰め込む
         */
        foreach( $columns as $column ){
            if( strlen( $this->requestObj->{$column} ) > 0){
                $insuMObj->{$column} = $this->requestObj->{$column};
            }else{
                $insuMObj->{$column} = null;
            }
        }
        
        // 接触対象が本人以外でなければ、接触対象者名は空
        if( $insuMObj->insu_contact_taisyo != 2 ){
            $insuMObj->insu_contact_taisyo_name = null;
        }
        
        // 獲得済か、更新済（同条件）または更新済（変更あり）の時に日付を追加
        if( in_array( $insuMObj->insu_status, ["6", "21", "22"] ) == True ){
            // 現在日時を指定
            $insuMObj->insu_add_tetsuduki_date = date("Y-m-d");

        }else{
            // 空の値を指定
            $insuMObj->insu_add_tetsuduki_date = NULL;
            
        }
        
        // スタッフ情報トス
        // チェックボックスが選択されていないときデフォルト値に0を入れる
        if( $insuMObj->insu_staff_info_toss == "" ){
            $insuMObj->insu_staff_info_toss = 0;
        }
        
        // スタッフ情報のトスが無の時
        if( $insuMObj->insu_staff_info_toss == 0 ){
            // スタッフ名を空にする
            $insuMObj->insu_toss_staff_name = NULL;
        }

        // 更新する
        $insuMObj->save();

        // 非表示が選択されているものは論理削除
        if( $this->requestObj->hidden_flg == 1 ){
            // 保険データの削除
            $insuMObj->delete();
        }
        
        // 更新のセッションを用意
        Session::put('update', 1);
        
        return $insuMObj;
    }
}
