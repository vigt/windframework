<?php
/**
 * @author Qiong Wu <papa0924@gmail.com> 2010-11-16
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2110 phpwind.com
 * @license 
 */

L::import('WIND:WindComponentModule');
/**
 * the last known user to change this file in the repository  <$LastChangedBy$>
 * @author Qiong Wu <papa0924@gmail.com>
 * @version $Id$ 
 * @package 
 */
abstract class WindAction extends WindComponentModule {

	protected $forward = null;

	protected $urlHelper = null;

	/**
	 * 默认的操作处理方法
	 */
	public function run() {}

	/**
	 * 根据路由信息重定向执行方法
	 * 
	 * @param AbstractWindRouter $handlerAdapter
	 */
	public function doAction($handlerAdapter) {
		$this->beforeAction();
		$this->setDefaultTemplateName($handlerAdapter);
		$this->resolvedActionMethod($handlerAdapter);
		$this->afterAction();
		
		return $this->getForward();
	}

	/**
	 * Action操作预处理方法，返回boolean型值
	 * 
	 * @return boolean
	 */
	public function beforeAction() {
		return true;
	}

	/**
	 * Action操作后处理方法，在执行完Action后执行
	 * 
	 * @return null
	 */
	public function afterAction() {}

	/**
	 * 重定向一个请求到另外的Action
	 * 
	 * @param string $action
	 * @param string $controller
	 * @param array $args
	 * @param boolean $isRedirect
	 */
	public function forwardAction($action = 'run', $controller = '', $args = array(), $isRedirect = false) {
		$this->getForward()->forwardAnotherAction($action, $controller, $args, $isRedirect);
	}

	/**
	 * 请求重定向到另外一个Url
	 */
	public function forwardRedirect($url) {
		$this->getForward()->setIsRedirect(true);
		$this->getForward()->setUrl($url);
	}

	/* 数据处理 */
	
	/**
	 * 设置模板数据
	 * 
	 * @param string|array|object $data
	 * @param string $key
	 */
	public function setOutput($data, $key = '') {
		$this->getForward()->setVars($data, $key);
	}

	/**
	 * 获得输入数据
	 * 如果输入了回调方法则返回数组:
	 * 第一个值：value
	 * 第二个值：验证结果
	 * 
	 * @param string $name input name
	 * @param string $type input type (GET POST COOKIE)
	 * @param string $callback | validation for input
	 * @return array | string
	 */
	public function getInput($name, $type = '', $callback = null) {
		if (is_array($name))
			return $this->getInputWithArray($name, $type);
		else
			return $this->getInputWithString($name, $type, $callback);
	}

	/* 模板处理 */
	/**
	 * 设置页面模板
	 * 
	 * @param string $template
	 */
	public function setTemplate($template) {
		$this->getForward()->getWindView()->setTemplateName($template);
	}

	/**
	 * 设置模板路径
	 * 
	 * @param string $templatePath
	 */
	public function setTemplatePath($templatePath) {
		$this->getForward()->getWindView()->setTemplatePath($templatePath);
	}

	/**
	 * 设置模板扩展名称
	 * 
	 * @param string $templateExt
	 */
	public function setTemplateExt($templateExt) {
		$this->getForward()->getWindView()->setTemplateExt($templateExt);
	}

	/**
	 * 设置页面布局
	 * 
	 * @param WindLayout $layout
	 */
	public function setLayout($layout) {
		$this->getForward()->getWindView()->setLayout($layout);
	}

	/* 错误处理 */
	
	/**
	 * 添加错误信息
	 * 
	 * @param string $message
	 * @param string $key
	 */
	public function addError($message, $key = '') {
		$this->error->addError($message, $key);
	}

	/**
	 * @param string $message
	 * @param string $key
	 */
	public function sendError($message = '', $key = '', $errorAction = '') {
		$this->addError($message, $key);
		$this->error->setErrorAction($errorAction);
		$this->error->sendError();
	}

	/**
	 * 返回UrlHelper对象
	 * 
	 * @return WindUrlHelper
	 */
	public function getUrlHelper() {
		if ($this->urlHelper === null) {
			throw new WindException('urlHelper', WindException::ERROR_CLASS_NOT_EXIST);
		}
		return $this->urlHelper;
	}

	/**
	 * @param WindUrlHelper $urlHelper
	 */
	public function setUrlHelper($urlHelper) {
		$this->urlHelper = $urlHelper;
	}

	/**
	 * @return WindForward
	 */
	public function getForward() {
		if ($this->forward === null) {
			throw new WindException('windForward', WindException::ERROR_CLASS_NOT_EXIST);
		}
		return $this->forward;
	}

	/**
	 * @param field_type $forward
	 */
	public function setForward($forward) {
		$this->forward = $forward;
	}

	/**
	 * 设置默认的模板名称
	 * 
	 * @param WindUrlBasedRouter $handlerAdapter
	 */
	protected function setDefaultTemplateName($handlerAdapter) {
		$_temp = $handlerAdapter->getAction() . '_' . $handlerAdapter->getController();
		$this->setTemplate($_temp);
	}

	/**
	 * 获得Action处理方法
	 * 
	 * @param AbstractWindRouter $handlerAdapter
	 */
	protected function resolvedActionMethod($handlerAdapter) {
		call_user_func_array(array($this, 'run'), array());
	}

	/**
	 * Enter description here ...
	 * 
	 * @param unknown_type $name
	 * @param unknown_type $type
	 * @return Ambigous <multitype:unknown mixed , string, unknown, multitype:>
	 */
	private function getInputWithString($name, $type = '', $callback = array()) {
		$value = '';
		switch ($type) {
			case 'form':
				$value = $this->response->getData($name);
				break;
			case IWindRequest::INPUT_TYPE_GET:
				$value = $this->request->getGet($name);
				break;
			case IWindRequest::INPUT_TYPE_POST:
				$value = $this->request->getPost($name);
				break;
			case IWindRequest::INPUT_TYPE_COOKIE:
				$value = $this->request->getCookie($name);
				break;
			default:
				$value = $this->request->getAttribute($name);
		}
		return $callback ? array($value, call_user_func_array($callback, array($value))) : $value;
	}

	/**
	 * Enter description here ...
	 * 
	 * @param string $name
	 * @param string $type
	 * @return array
	 */
	private function getInputWithArray($name, $type = '') {
		$result = array();
		foreach ($name as $key => $value) {
			$result[(is_array($value) ? $key : $value)] = $this->getInput($value, $type);
		}
		return $result;
	}

}