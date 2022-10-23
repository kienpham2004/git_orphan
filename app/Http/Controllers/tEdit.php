<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchRequest;

// 車点検
use App\Commands\Modal\Syatenken\SyatenkenFindByIdCommand;
use App\Commands\Modal\Syatenken\SyatenkenUpdateByIdCommand;
use App\Commands\Modal\Syatenken\SyatenkenUpdateMemoByIdCommand;
use App\Commands\Modal\Syatenken\SyatenkenCommentFindByIdCommand;
use App\Commands\Modal\Syatenken\FindSyakenByTenkenIdCommand;
// クレジット
use App\Commands\Modal\Credit\CreditFindByIdCommand;
use App\Commands\Modal\Credit\CreditUpdateByIdCommand;
// 保険
use App\Commands\Modal\Hoken\InsuranceFindByIdCommand;
use App\Commands\Modal\Hoken\InsuranceUpdateByIdCommand;
use App\Commands\Modal\Hoken\InsuranceUpdateMemoByIdCommand;
use DB;
/**
 * モーダルの表示と更新用のトレイト
 */
trait tEdit{

    ########################
    ## 実施リスト
    ########################
    
    ########################
    ## 車点検
    ########################
    
    /**
     * 車点検リストや活動進捗グラフの詳細情報
     * Ajax通信の時、collectionは勝手にjsonになる
     * @param  integer $id 対象id
     * @param  integer $editFlg 1:編集,2：詳細
     * @param  integer $tenkenFlg 0:車検,1：点検
     * @return json    指定idの詳細情報
     */
    public function getSyatenkenEdit( $id, $editFlg = 1, $tenkenFlg = 0){
        // 指定されたidの車点検のデータを取得
        $data = $this->dispatch(new SyatenkenFindByIdCommand( $id ));

        // 値を初期化
        if( $data->mi_rstc_reserve_commit_date == "1970-01-01 09:00:00" ){
            $data->mi_rstc_reserve_commit_date = NULL;
        }
        if( $data->mi_rstc_start_date == "1970-01-01 09:00:00" ){
            $data->mi_rstc_start_date = NULL;
        }
        if( $data->mi_rstc_get_out_date == "1970-01-01 09:00:00" ){
            $data->mi_rstc_get_out_date = NULL;
        }
        
        if( !empty( $data->mi_rstc_reserve_commit_date ) ){
            $data->mii_rstc_reserve_commit_date = date( 'Y-m-d', strtotime( $data->mi_rstc_reserve_commit_date ) );
        }

        if( !empty( $data->mi_rstc_start_date ) ){
            $data->mi_rstc_start_date = date( 'Y-m-d', strtotime( $data->mi_rstc_start_date ) );
        }

        if( !empty( $data->mi_rstc_get_out_date ) ){
            $data->mi_rstc_get_out_date = date( 'Y-m-d', strtotime( $data->mi_rstc_get_out_date ) );
        }

        // 最新コメント5件の取得
        $comment = $this->dispatch(
            new SyatenkenCommentFindByIdCommand( $id )
        );

        // リコール情報取得
        $data->recall = $this->getRecallInfo($data->tgc_car_manage_number);
        // 編集フラグ
        $data -> editFlg = $editFlg; // 編集と詳細の判定フラグ
        return view(
            'modal.syatenken',
            compact(
                'data',
                'comment',
                'tenkenFlg'
            )
        );
    }

    /**
     * 車点検のモーダルの更新処理
     * @param  integer        $id      対象のid
     * @param  SearchRequest $requestObj 入力項目
     * @return json          ajaxが成功した時のステータス
     */
    public function getSyatenkenEditSubmit( $id, SearchRequest $requestObj ){
        // 指定されたidの車点検のデータを編集
        $this->dispatch(
            new SyatenkenUpdateByIdCommand( $id, $requestObj )
        );
    }

//    /**
//     * 車点検リストや活動進捗グラフの詳細情報
//     * Ajax通信の時、collectionは勝手にjsonになる
//     * @param  integer $id 対象id
//     * @return json    指定idの詳細情報
//     */
//    public function getSyatenkenDetail( $id ){
//        // 指定されたidの車点検のデータを取得
//        $data = $this->dispatch(
//            new SyatenkenFindByIdCommand( $id )
//        );
//
//        // 値を初期化
//        if( $data->mi_rstc_reserve_commit_date == "1970-01-01 09:00:00" ){
//            $data->mi_rstc_reserve_commit_date = NULL;
//        }
//        if( $data->mi_rstc_start_date == "1970-01-01 09:00:00" ){
//            $data->mi_rstc_start_date = NULL;
//        }
//        if( $data->mi_rstc_get_out_date == "1970-01-01 09:00:00" ){
//            $data->mi_rstc_get_out_date = NULL;
//        }
//
//        if( !empty( $data->mi_rstc_reserve_commit_date ) ){
//            $data->mii_rstc_reserve_commit_date = date( 'Y-m-d', strtotime( $data->mi_rstc_reserve_commit_date ) );
//        }
//
//        if( !empty( $data->mi_rstc_start_date ) ){
//            $data->mi_rstc_start_date = date( 'Y-m-d', strtotime( $data->mi_rstc_start_date ) );
//        }
//
//        if( !empty( $data->mi_rstc_get_out_date ) ){
//            $data->mi_rstc_get_out_date = date( 'Y-m-d', strtotime( $data->mi_rstc_get_out_date ) );
//        }
//
//        // 最新コメント5件の取得
//        $comment = $this->dispatch(
//            new SyatenkenCommentFindByIdCommand( $id )
//        );
//
//        // リコール情報取得
//        $data->recall = $this->getRecallInfo($data->tgc_car_manage_number);
//
//        return view(
//            'modal.syatenken_detail',
//            compact(
//                'data',
//                'comment'
//            )
//        );
//    }

    /**
     * メモ情報を登録
     * Ajax通信の時、collectionは勝手にjsonになる
     * @param  integer $id 対象id
     * @param  integer $type 1:車点検,　2:保険
     * @return json    指定idの詳細情報
     */
    public function getDentatsuMemo( $id, $type = 1){

        if ($type == 1) { // 指定されたidの車点検のデータを取得
            $data = $this->dispatch(new SyatenkenFindByIdCommand($id));
            $data->alert_memo = $data->tgc_alert_memo;
        }
        elseif($type == 2){ // 保険
            $data = $this->dispatch(new InsuranceFindByIdCommand($id));
            $data->alert_memo = $data->insu_alert_memo;
        }

        $data->type = $type;
        return view(
            'modal.dentatsu_memo',
            compact(
                'data'
            )
        );
    }

    /**
     * 伝達事項のモーダルの更新処理
     * @param  integer        $id      対象のid
     * @param  SearchRequest $requestObj 入力項目
     * @param  integer $type 1:車点検,　2:保険
     * @return json          ajaxが成功した時のステータス
     */
    public function getDentatsuMemoSubmit( $id, SearchRequest $requestObj,$type = 1 ){

        if ($type == 1) {// 指定されたidの車点検のデータを編集
            $this->dispatch(new SyatenkenUpdateMemoByIdCommand( $id, $requestObj ));
        }
        elseif($type == 2){// 保険
            $this->dispatch(new InsuranceUpdateMemoByIdCommand( $id, $requestObj ));
        }
    }

    ########################
    ## クレジット
    ########################
    
    /**
     * 車点検リストや活動進捗グラフの詳細情報
     * Ajax通信の時、collectionは勝手にjsonになる
     * @param  integer $id 対象id
     * @return json    指定idの詳細情報
     */
    public function getCreditEdit( $id ){
        // 指定されたidの車点検のデータを取得
        $data = $this->dispatch(
            new CreditFindByIdCommand( $id )
        );
             
        return view(
            'modal.credit',
            compact(
                'data'
            )
        );
    }
    
    /**
     * 車点検のモーダルの更新処理
     * @param  integer        $id      対象のid
     * @param  SearchRequest $requestObj 入力項目
     * @return json          ajaxが成功した時のステータス
     */
    public function getCreditEditSubmit( $id, SearchRequest $requestObj ){
        // 指定されたidの車点検のデータを編集
        $this->dispatch(
            new CreditUpdateByIdCommand( $id, $requestObj )
        );
    }

    ########################
    ## 保険
    ########################
    
    /**
     * 車点検リストや活動進捗グラフの詳細情報
     * Ajax通信の時、collectionは勝手にjsonになる
     * @param  integer $id 対象id
     * @return json    指定idの詳細情報
     */
    public function getHokenEdit( $id, $editFlg, $honsyaFlg="" ){
        // 指定されたidの車点検のデータを取得
        $data = $this->dispatch(
            new InsuranceFindByIdCommand( $id )
        );
        $data -> editFlg = $editFlg; // 編集と詳細の判定フラグ
                
        return view(
            'modal.hoken',
            compact(
                'data',
                'honsyaFlg'
            )
        );
    }

    /**
     * 車点検のモーダルの更新処理
     * @param  integer        $id      対象のid
     * @param  SearchRequest $requestObj 入力項目
     * @return json          ajaxが成功した時のステータス
     */
    public function getHokenEditSubmit( $id, SearchRequest $requestObj ){
        // 指定されたidの車点検のデータを編集
        $this->dispatch(
            new InsuranceUpdateByIdCommand( $id, $requestObj )
        );
    }

    /**
     * リコール情報取得
     * @param $car_manage_number　管理番号
     * @return リコール情報
     */
    private function getRecallInfo( $car_manage_number){
        // リコール情報取得
        $sql = "select recall_no,recall_division,recall_detail,recall_jisshibi 
                from tb_recall 
                where recall_car_manage_number = '{$car_manage_number}' 
                order by recall_jisshibi asc;";
        return DB::select($sql);
    }
}
