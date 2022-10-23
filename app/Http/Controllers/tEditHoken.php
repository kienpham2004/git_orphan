<?php

namespace App\Http\Controllers;

use App\Original\Util\SessionUtil;
use App\Commands\Hoken\Tsuika\CreateTsuikaCommand;
use App\Commands\Hoken\Tsuika\UpdateTsuikaCommand;
use App\Commands\Hoken\Kaiyaku\CreateKaiyakuCommand;
use App\Commands\Hoken\Kaiyaku\UpdateKaiyakuCommand;
use App\Models\Insurance;
use App\Models\InsuranceKaiyaku;
use App\Http\Requests\SearchRequest;
use App\Http\Requests\InsuranceRequest;
use App\Http\Requests\InsuranceKaiyakuRequest;
use App\Models\Base;
/**
 * モーダルの表示と更新用のトレイト
 */
trait tEditHoken{

    /**
     * 新規登録画面の表示
     * @param  SearchRequest $requestObj [description]
     * @return [type]                 [description]
     */
    public function getCreate( SearchRequest $requestObj ){

        // 権限を調べ為の値を取得
        $loginAccountObj = SessionUtil::getUser();

        // 保険モデルオブジェクトを取得
        $insuranceMObj = new Insurance();
        $insuranceMObj->base_code = $loginAccountObj->getBaseCode();                    // 拠点コード
        $insuranceMObj->base_name = Base::options()[$loginAccountObj->getBaseCode()];   // 拠点名
        $insuranceMObj->user_id = $loginAccountObj->getUserId();                        // 担当者コード
        $insuranceMObj->user_name = $loginAccountObj->getUserName();                    // 担当者名

        return view(
            $this->displayObj->category . '.order.input',
            compact(
                'insuranceMObj'
            )
        )
        ->with( "title", "新規顧客追加" )
        ->with( 'displayObj', $this->displayObj )
        ->with( "type", "create" )
        ->with( "buttonId", 'regist-button' );
    }

    /**
     * 登録画面で入力された値を登録
     * @param  UserRequest $requestObj [description]
     * @return [type]                  [description]
     */
    public function putCreate( InsuranceRequest $requestObj ) {
        // 登録画面で入力された値を登録
        $this->dispatch(
            new CreateTsuikaCommand( $requestObj )
        );
        
        return redirect( action( $this->displayObj->ctl . '@getIndex' ) );
    }

    #######################
    ## 編集画面
    #######################
    
    /**
     * 編集画面を開く時の画面
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getEdit( $id ) {
        // 保険追加モデルオブジェクトを取得
        $insuranceMObj = Insurance::findOrFail( $id );
        
        return view(
            $this->displayObj->category . '.order.input',
            compact(
                'insuranceMObj'
            )
        )
        ->with( "title", "保険追加／編集" )
        ->with( 'displayObj', $this->displayObj )
        ->with( "type", "edit" )
        ->with( "buttonId", 'update-button' );
    }
    
    /**
     * 編集画面で入力された値を登録
     * @param  [type]      $id         [description]
     * @param  BaseRequest $requestObj [description]
     * @return [type]                  [description]
     */
    public function putEdit( $id, InsuranceRequest $requestObj ) {
        // 編集画面で入力された値を更新
        $this->dispatch(
            new UpdateTsuikaCommand( $id, $requestObj )
        );
        
        return redirect( action( $this->displayObj->ctl . '@getSearch' ) );
    }
    

    /**
     * その他異動・解約の登録画面の表示
     * @param  SearchRequest $requestObj [description]
     * @return [type]                 [description]
     */
    public function getCreateKaiyaku( SearchRequest $requestObj ){
        // 保険モデルオブジェクトを取得
        $insuranceMObj = new InsuranceKaiyaku();

        // 権限を調べ為の値を取得
        $loginAccountObj = SessionUtil::getUser();
        
        $insuranceMObj->insu_base_code = $this->selectedBaseCode();
        
        $insuranceMObj->insu_user_name = $this->selectedUserName();

        return view(
            $this->displayObj->category . '.kaiyaku.input',
            compact(
                'insuranceMObj'
            )
        )
        ->with( "title", "その他異動・解約 追加／登録" )
        ->with( 'displayObj', $this->displayObj )
        ->with( "type", "create" )
        ->with( "buttonId", 'regist-button' );
    }

    /**
     * その他異動・解約の登録画面で入力された値を登録
     * @param  UserRequest $requestObj [description]
     * @return [type]                  [description]
     */
    public function putCreateKaiyaku( InsuranceKaiyakuRequest $requestObj ) {
        // 登録画面で入力された値を登録
        $this->dispatch(
            new CreateKaiyakuCommand( $requestObj )
        );
        
        return redirect( action( $this->displayObj->ctl . '@getIndex' ) );
    }

    #######################
    ## 編集画面
    #######################
    
    /**
     * その他異動・解約の編集画面を開く時の画面
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getEditKaiyaku( $id ) {
        // 保険追加モデルオブジェクトを取得
        $insuranceMObj = InsuranceKaiyaku::findOrFail( $id );
        
        return view(
            $this->displayObj->category . '.kaiyaku.input',
            compact(
                'insuranceMObj'
            )
        )
        ->with( "title", "その他異動・解約 追加／編集" )
        ->with( 'displayObj', $this->displayObj )
        ->with( "type", "edit" )
        ->with( "buttonId", 'update-button' );
    }
    
    /**
     * その他異動・解約の編集画面で入力された値を登録
     * @param  [type]      $id         [description]
     * @param  BaseRequest $requestObj [description]
     * @return [type]                  [description]
     */
    public function putEditKaiyaku( $id, InsuranceKaiyakuRequest $requestObj ) {
        // 編集画面で入力された値を更新
        $this->dispatch(
            new UpdateKaiyakuCommand( $id, $requestObj )
        );
        
        return redirect( action( $this->displayObj->ctl . '@getSearch' ) );
    }

}
