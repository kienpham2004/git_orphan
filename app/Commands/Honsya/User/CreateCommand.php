<?php

namespace App\Commands\Honsya\User;

use App\Lib\Util\DateUtil;
use App\Models\UserAccount;
use App\Commands\Command;
use App\Http\Requests\UserRequest;

/**
 * 担当者を新規作成するコマンド
 *
 * @author yhatsutori
 */
class CreateCommand extends Command{
    
    /**
     * コンストラクタ
     * @param UserRequest $requestObj [description]
     */
    public function __construct( UserRequest $requestObj, $file_name='' ){
        $this->requestObj = $requestObj;
        // 顔写真用ファイル名
        $this->file_name = $file_name;
    }

    /**
     * メインの処理
     * @return [type] [description]
     */
    public function handle(){
        // 追加する値の配列を取得
        $setValues = $this->requestObj->all();
        
        // 顔画像が選択された時
        if ( !empty( $this->file_name ) ){
            $setValues['file_name'] = "FaceImages/{$this->file_name}";
            $setValues['img_uploaded_flg'] = 1;
            $setValues['img_uploaded_at'] = DateUtil::now();
        }

        // 暗号化パスワードの指定
        $setValues['password'] = $setValues['user_password'];

        // 登録されたデータを持つモデルオブジェクトを取得
        $userMObj = UserAccount::create( $setValues );

        return $userMObj;
    }

}
