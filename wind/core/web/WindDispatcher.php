<?php
/**
 * @author Qiong Wu <papa0924@gmail.com> 2010-12-15
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2110 phpwind.com
 * @license 
 */

L::import('WIND:core.WindComponentModule');
/**
 * 请求分发
 *
 * the last known user to change this file in the repository  <$LastChangedBy$>
 * @author Qiong Wu <papa0924@gmail.com>
 * @version $Id$
 * @package 
 */
class WindDispatcher extends WindComponentModule {

	/**
	 * 请求分发处理
	 * 
	 * @param WindForward $forward
	 */
	public function dispatch($forward) {
		if ($forward->getIsRedirect())
			$this->dispatchWithRedirect($forward);
		elseif ($forward->getIsReAction()) {
			$this->dispatchWithAction($forward);
		} else
			$this->render($forward);
	}

	/**
	 * 请求分发一个重定向请求
	 * 
	 * @param WindForward $forward
	 */
	protected function dispatchWithRedirect($forward) {
		$_url = $forward->getUrl();
		//TODO check $_url
		$urlHelper = $this->windFactory->getInstance(COMPONENT_URLHELPER);
		if (!$_url && $forward->getIsReAction()) {
			/* @var $urlHelper WindUrlHelper */
			$_url = $urlHelper->createUrl($forward->getAction(), $forward->getController(), $forward->getArgs());
		}
		$_url = $urlHelper->checkUrl($_url);
		$this->response->sendRedirect($_url);
	}

	/**
	 * 请求分发一个操作请求
	 * @param WindForward $forward
	 */
	protected function dispatchWithAction($forward) {
		if (!$_a = $forward->getAction()) throw new WindException('Incorrect action value ' . $_a . ' .');
		
		/* @var $_router WindUrlBasedRouter */
		$_router = $this->windFactory->getInstance(COMPONENT_ROUTER);
		$_router->setAction($_a);
		
		list($_c, $_m) = W::resolveController($forward->getController());
		$_c && $_router->setController($_c);
		$_m && $_router->setModule($_m);
		
		$appName = $this->windSystemConfig->getAppClass();
		$application = $this->windFactory->getInstance($appName);
		$application->processRequest();
	}

	/**
	 * 进行视图渲染
	 * 
	 * @param WindForward $forward
	 */
	protected function render($forward) {
		if ($forward && null !== ($windView = $forward->getWindView())) {
			if ($windView->getTemplateName() === '') return;
			$viewResolver = $windView->getViewResolver();
			$viewResolver->windAssign($forward->getVars());
			
			$this->response->setBody($viewResolver->windFetch(), $windView->getTemplateName());
		} else {
			throw new WindException('unable to create the object with forward.');
		}
	}
}
