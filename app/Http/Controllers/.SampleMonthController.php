<?php
//
//namespace App\Http\Controllers;
//
//use App\Original\Util\SessionUtil;
//use App\Commands\Mikomi\MikomiListCommand;
//use App\Http\Requests\SearchRequest;
//use App\Http\Controllers\Controller;
//use App\Http\Controllers\tInitSearch;
//use App\Http\Controllers\tMonthSearch;
//
///**
// * 月またぎサンプルコントローラー
// *
// * @author yhatsutori
// *
// */
//class SampleMonthController extends Controller{
//
//	use tInitSearch, tMonthSearch;
//
//	/**
//	 * 次月、当月、前月で進める対象のアイテム名
//	 * ＊実態はtMonthSearchトレイト
//	 */
//	protected function targetItemName() {
//		return 'mkm_inspection_ym_from';
//	}
//
//	/**
//	 * 前ページの処理
//	 *
//	 * @param SearchRequest $requestObj
//	 * @return \Illuminate\View\View
//	 */
//	public function postPrev( SearchRequest $requestObj) {
//		$requestObj = $this->prevRequest($requestObj);
//		$search = $this->simpleSearchCondition($requestObj);
//
//		// 検索値を登録(セッション)
//		SessionUtil::putSearch($search);
//
//		$mikomiList = $this->dispatch(
//			new MikomiListCommand(
//				$this->getSortParams()
//				, $requestObj
//				, $this->sortUrl()
//			)
//		);
//
//		return view('act.mikomi.search', compact('search', 'mikomiList'));
//	}
//
//	/**
//	 * 次ページの処理
//	 *
//	 * @param SearchRequest $requestObj
//	 * @return \Illuminate\View\View
//	 */
//	public function postNext(SearchRequest $requestObj) {
//		$requestObj = $this->nextRequest($requestObj);
//		$search = $this->simpleSearchCondition($requestObj);
//
//		// 検索値を登録(セッション)
//		SessionUtil::putSearch($search);
//
//		$mikomiList = $this->dispatch(
//			new MikomiListCommand(
//				$this->getSortParams()
//				, $requestObj
//				, $this->sortUrl()
//			)
//		);
//
//		return view('act.mikomi.search', compact('search', 'mikomiList'));
//	}
//
//	/**
//	 * 当月ボタンの処理
//	 *
//	 * @param SearchRequest $requestObj
//	 */
//	public function postCurrent(SearchRequest $requestObj) {
//		$requestObj = $this->currentRequest($requestObj);
//		$search = $this->simpleSearchCondition($requestObj);
//
//		// 検索値を登録(セッション)
//		SessionUtil::putSearch($search);
//
//		$mikomiList = $this->dispatch(
//			new MikomiListCommand(
//				$this->getSortParams()
//				, $requestObj
//				, $this->sortUrl()
//			)
//		);
//
//		return view('act.mikomi.search', compact('search', 'mikomiList'));
//	}
//}
