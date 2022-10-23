<?php

namespace App\Http\Controllers\Honsya;

use App\Original\Util\SessionUtil;
use App\Models\UserAccount;
use App\Commands\Honsya\User\ListCommand;
use App\Commands\Honsya\User\CreateCommand;
use App\Commands\Honsya\User\UpdateCommand;
use App\Commands\Honsya\User\DeleteFaceImageCommand;
use App\Http\Requests\UserRequest;
use App\Http\Controllers\Controller;
use App\Http\Controllers\tInitSearch;
use App\Lib\Util\DateUtil;
use App\Lib\Util\Constants;
Use Exception;
// 独自
use OhInspection;

class UserController extends Controller {

	use tInitSearch;

	/**
	 * コンストラクタ
	 */
	public function __construct(){
		// 表示部分で使うオブジェクトを作成
		$this->initDisplayObj();
	}

	#######################
	## initalize
	#######################

	/**
	 * 表示部分で使うオブジェクトを作成
	 * @return [type] [description]
	 */
	public function initDisplayObj(){
		// 表示部分で使うオブジェクトを作成
		$this->displayObj = app('stdClass');
		// カテゴリー名
		$this->displayObj->category = "honsya";
		// 画面名
		$this->displayObj->page = "user";
		// 基本のテンプレート
		$this->displayObj->tpl = $this->displayObj->category . "." . $this->displayObj->page;
		// コントローラー名
		$this->displayObj->ctl = "Honsya\UserController";
	}

	#######################
	## 検索・並び替え
	#######################
        /**
         * [extendSortParams description]
         * @return [type] [description]
         */
        public function extendSortParams() {
            // 複数テーブルにあるidが重複するため明示的にエイリアス指定
            $sort = [ 'tb_user_account.base_id' => 'asc' ];

            return $sort;
        }

        /**
         * 検索部分のデフォルト値を指定
         * @return [type] [description]
         */
	public function extendSearchParams() {
        // ユーザー情報を取得(セッション)
        $loginAccountObj = SessionUtil::getUser();
        if (in_array($loginAccountObj->getRolePriority(), [1, 2, 3]))  //管理者、部長、本社
        {
            $search['base_id'] = '';
        } else if (in_array($loginAccountObj->getRolePriority(), [4])) // 店長
        {
            $search['base_id'] = $loginAccountObj->getBaseId();
        } elseif (in_array($loginAccountObj->getRolePriority(), [5])) // 工場長
        {
            $search['base_id'] = $loginAccountObj->getBaseId();
        } else // 営業担当、サービス
        {
            $search['base_id'] = $loginAccountObj->getBaseId();
            $search['user_id'] = $this->selectedUserId();
        }
		return $search;
	}

	#######################
	## 一覧画面
	#######################

	/**
	 * 一覧画面のデータを表示
	 * @param  [type] $search  [description]
	 * @param  array $sort    [description]
	 * @param  object $requestObj [description]
	 * @return [type]             [description]
	 */
	public function showListData( $search, $sort, $requestObj ){
		// 表示するデータを取得
		$showData = $this->dispatch(new ListCommand($sort, $requestObj, SessionUtil::getUser()));

        //　表示用に、並び替え情報を取得
        if( isset( $sort['sort'] ) == True && !empty( $sort['sort'] ) == True ){
            foreach ( $sort['sort'] as $key => $value ) {
                // 並び替え情報を格納
                $sortTypes = [
                    'sort_key' => $key,
                    "sort_by" => $value
                ];
            }
        }

        // 処理時間を記録
        $runTime = DateUtil::getRunTime();
        return view(
			$this->displayObj->tpl . '.index',
			compact(
				'search',
				'sortTypes',
				'showData',
                'runTime'
			)
		)
		->with( "title", "担当者／一覧" )
		->with( 'displayObj', $this->displayObj )
                ->with( "sortUrl", action( $this->displayObj->ctl . '@getSort' ) );
	}

	#######################
	## 追加画面
	#######################

	/**
	 * 登録画面を開く時の画面
	 * @return [type] [description]
	 */
	public function getCreate() {
		// 担当者モデルオブジェクトを取得
		$userMObj = new UserAccount();
        $userMObj->del_flg = false;

        // 処理時間を記録
        $runTime = DateUtil::getRunTime();
		return view($this->displayObj->tpl . '.input',
			compact('userMObj', 'runTime')
		)
		->with( "title", "担当者／登録" )
		->with( 'displayObj', $this->displayObj )
		->with( "type", "create" )
		->with( "buttonId", 'regist-button' );
	}

	/**
	 * 登録画面で入力された値を登録
	 * @param  UserRequest $requestObj [description]
	 * @return [type]                  [description]
	 */
	public function putCreate( UserRequest $requestObj ) {
		$new_name = '';
		
		/** @var string 画像ファイル名 */
		$img_name = OhInspection::getImageName('face-image');
		
		if (! is_null($img_name)) {
			/** @var string 画像の拡張子 */
			$extension = OhInspection::getImageExtension($img_name);
			/** @var string 新しいファイル名（例：999.jpg） */
			$new_name = $requestObj->user_id.'.'.$extension;
			/** 画像ファイルを移動させる処理 */
			OhInspection::moveImage( 'face-image', 'FaceImages', $new_name );
		}
		
		// 登録画面で入力された値を登録
		$this->dispatch(
			new CreateCommand( $requestObj, $new_name )
		);

        // ユーザー権限一覧を取得する
        SessionUtil::put(Constants::SEC_ACCOUT_PERMISION, UserAccount::allStaffPermision() );

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
	public function getEdit( $id = "", $editFlag = "" )
    {
        $errorMessage = "";
        $userMObj = null;
        // 担当者モデルオブジェクトを取得
        //$userMObj = UserAccount::findOrFail( $id );
        // 入力値不正の場合
        if ($id == "" || !is_numeric($id)) {
            $errorMessage = "アカウント情報が取得だきません";
        }
        else{
            try {
                $userMObj = UserAccount::getStaffbyId($id);
                // ログインしているアカウント情報
                $loginAccountObj = SessionUtil::getUser();

                // $idを直接入力したとき、編集権限がないにもかかわらず
                // 編集できてしまうのを防ぐため。
                if ( ! in_array( $loginAccountObj->getRolePriority(), [1,2,3,4,5] ) ) {
                    // 編集しているのが、ログインユーザーと一致するかを調べる
                    if ( $loginAccountObj->getPrimayKey() !== $userMObj->id ) {
                        $errorMessage = "本人以外の情報を編集できません";
                    }
                }elseif ( in_array( $loginAccountObj->getRolePriority(), [1,2,3] ) ) {
                    // 自権限以下ではない
                    if ($userMObj->account_level < $loginAccountObj->getRolePriority() ) {
                        $errorMessage = "編集できる権限がありません";
                    }
                }elseif ( in_array( $loginAccountObj->getRolePriority(), [4,5] ) ) {
                    // 自権限以下且つ自拠点ではない
                    if ($userMObj->account_level < $loginAccountObj->getRolePriority() ||
                        $userMObj->base_id != $loginAccountObj->getBaseid()  ) {
                        $errorMessage = "編集できる権限がありません";
                    }
                }

                // 削除フラグ設定
                $userMObj->del_flg = false;
                if(isset($userMObj->deleted_at)){
                    $userMObj->del_flg = true;
                }
            } catch (ModelNotFoundException $e) {
                \Log::error( $e );
                $errorMessage = "アカウント情報が取得だきません";
            }
        }

        $type = "view";
        if($editFlag == "1"){
            $type = "edit";
        }

        return view(
			$this->displayObj->tpl . '.input',
			compact('userMObj', 'errorMessage')
		)
		->with( "title", "担当者／編集" )
		->with( 'displayObj', $this->displayObj )
		->with( "type", $type )
		->with( "buttonId", 'update-button' );
	}

	/**
	 * 編集画面で入力された値を登録
	 * @param  [type]      $id         [description]
	 * @param  UserRequest $requestObj [description]
	 * @return [type]                  [description]
	 */
	public function putEdit( $id, UserRequest $requestObj ) {
		$new_name = '';

		/** @var string 画像ファイル名 */
		$img_name = OhInspection::getImageName('face-image');

		if (! is_null($img_name)) {
			/** @var string 画像の拡張子 */
			$extension = OhInspection::getImageExtension($img_name);
			/** @var string 新しいファイル名（例：999.jpg） */
			$new_name = $requestObj->user_id.'.'.$extension;
			/** 画像ファイルを移動させる処理 */
			OhInspection::moveImage( 'face-image', 'FaceImages', $new_name );
		}


		// 編集画面で入力された値を更新
		$this->dispatch(
			new UpdateCommand( $id, $requestObj, $new_name )
		);

        // ユーザー権限一覧を取得する
        SessionUtil::put(Constants::SEC_ACCOUT_PERMISION, UserAccount::allStaffPermision() );

        return redirect( action( $this->displayObj->ctl . '@getSearch' ) );
	}

	/**
	 * 顔写真の削除機能
	 * @param  string $id ログインしているユーザーID
	 * @return json
	 */
	public function deleteFaceImage($user_id)
	{
		// 削除
		$isSuccess = $this->dispatch(new DeleteFaceImageCommand($user_id));

		return response()->json([
			'status' => $isSuccess
		]);
	}
	
}
