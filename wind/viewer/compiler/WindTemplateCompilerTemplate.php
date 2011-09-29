<?php
Wind::import('WIND:viewer.AbstractWindTemplateCompiler');
/**
 * 编译模板标签
 * 
 * 支持属性:
 * source: 模板文件源地址
 * suffix: 模板文件后缀
 * load: 是否将编译内容加载到本模板中
 * <code><template source='' suffix='' load='false' /></code>
 *
 * @author Qiong Wu <papa0924@gmail.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id$
 * @package wind.viewer.compiler
 */
class WindTemplateCompilerTemplate extends AbstractWindTemplateCompiler {
	protected $source = '';
	protected $suffix = '';
	protected $load = 'true';

	/* (non-PHPdoc)
	 * @see AbstractWindTemplateCompiler::compile()
	 */
	public function compile($key, $content) {
		if (!$this->source) return $content;
		
		if (preg_match('/^{?\$(\w+)}?$/Ui', $this->source, $_tmp)) {
			$content = '<?php if (!isset($' . $this->source . '))';
			$content .= 'list($' . $this->source . ') = $__viewer->compile(' . $this->source . ', ' . $this->suffix . ');';
			$content .= 'include_once($' . $this->source . ');?>';
		} else {
			if ($this->load === 'false') {
				list($compileFile) = $this->windViewerResolver->compile($this->source, $this->suffix);
				if (!empty($_tmp)) $compileFile = str_replace($this->source, '{$' . $_tmp[1] . '}', $compileFile);
				$content = '<?php include("' . addslashes($compileFile) . '"); ?>';
			} else {
				list(, $content) = $this->windViewerResolver->compile($this->source, $this->suffix, true);
			}
		}
		return $content;
	}

	/* (non-PHPdoc)
	 * @see AbstractWindTemplateCompiler::getProperties()
	 */
	public function getProperties() {
		return array('source', 'suffix', 'load');
	}

}

?>