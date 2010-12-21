<?php
/**
 * @author xiaoxia xu <x_824@sina.com> 2010-11-22
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2110 phpwind.com
 * @license 
 */

/**
 * 配置文件解析类
 * 配置文件格式允许有4中格式：xml, php, properties, ini
 * 
 * 根据用户传入的配置文件所在位置解析配置文件，
 * 并将生成的配置缓存文件， 以php格式默认放在‘COMPILE_PATH’下面
 * 
 * the last known user to change this file in the repository  <$LastChangedBy$>
 * @author xiaoxia xu <x_824@sina.com>
 * @version $Id$ 
 * @package
 */
class WindConfigParser {
	const ISMERGE = 'isMerge';
	/**
	 * 配置文件支持的格式白名单
	 * @var 
	 */
	const CONFIG_XML = 'XML';
	const CONFIG_PHP = 'PHP';
	const CONFIG_INI = 'INI';
	const CONFIG_PROPERTIES = 'PROPERTIES';
	
	/**
	 * 框架缺省配置文件的名字
	 * @var string $defaultConfig 
	 */
	private $windConfig = 'wind_config';
	
	/**
	 * 配置解析对象
	 * @var object $configParser
	 */
	private $configParser = null;
	/**
	 * 当前配置解析的格式
	 * @var string $parserFormat
	 */
	private $parseFormat = 'XML';
	
	/**
	 * 配置文件解析出来的数据编码
	 * @var string $encoding
	 */
	private $encoding = 'UTF-8';
	
	/**
	 * ISMERGE的有效值范围
	 * @var array $mergeValue
	 */
	private $mergeValue = array('true', '1');
	
	/**
	 * 初始化
	 * 设置解析数据输出的编码方式
	 * @param String $outputEncoding	
	 */
	public function __construct($outputEncoding = 'UTF-8') {
		$this->encoding = $outputEncoding;
	}
	
	/**
	 * 1、缺省的配置文件，读取框架提供的php格式返回
	 * 2、如果输入的配置文件格式没有提供支持，则抛出异常
	 * 3、根据格式进行解析
	 * @param string $currentAppName 当前应用的名字
	 * @param string $configPath 当前应用配置文件地址
	 * @return array 解析成功返回的数据
	 */
	public function parser($currentAppName, $configPath = '') {
		$currentAppName = strtolower(trim($currentAppName));
		$cacheFileName = $currentAppName . '_config.php';
		if ($this->isCompiled($cacheFileName)) {
			return include $cacheFileName;
		}
		$configPath = trim($configPath);
		$this->parseFormat = $this->getConfigFormat($configPath);
		$userConfig = $this->doParser($configPath);
		$defaultConfig = $this->doParser($this->getWindConfigPath());
		$userConfig = $this->mergeConfig($defaultConfig, $userConfig);
		
		$this->saveConfigFile($cacheFileName, $userConfig);
		return $userConfig;
	}
	
	/**
	 * 初始化配置文件解析器
	 * @access private
	 * 
	 */
	private function createParser() {
		switch ($this->parseFormat) {
			case self::CONFIG_XML:
				L::import("WIND:component.parser.WindXmlParser");
				return new WindXmlParser('1.0', $this->encoding);
				break;
			case self::CONFIG_INI:
				L::import("WIND:component.parser.WindIniParser");
				return new WindIniParser();
				break;
			case self::CONFIG_PROPERTIES:
				L::import("WIND:component.parser.WindPropertiesParser");
				return new WindPropertiesParser();
				break;
			default:
				throw new WindException('init config parser error.');
				break;
		}
	}
	
	/**
	 * 执行解析并返回解析结果
	 * 接收一个配置文件路径，根据路径信息初始化配置解析器，并解析该配置
	 * 以数组格式返回配置解析结果
	 * 
	 * @param string $configFile
	 * @return array
	 */
	private function doParser($configFile) {
		if (!$configFile) return array();
		if ($this->parseFormat == 'PHP') {
			return include($configFile);
		}
		if ($this->configParser === null) {
			$this->configParser = $this->createParser();
		}
		return $this->configParser->parse($configFile);
	}
	
	/**
	 * 合并配置文件
	 * 
	 * 如果应用配置中没有配置相关选项，则使用默认配置中的选项
	 * 如果是需要合并的项，则将缺省项和用户配置项进行合并，合并规则为用户配置优先级大于缺省配置
	 * 
	 * @param array $defaultConfig 默认的配置文件
	 * @param array $appConfig 应用的配置文件
	 * @return array  合并后的配置
	 */
	private function mergeConfig($defaultConfig, $appConfig) {
		if (!$defaultConfig || !is_array($defaultConfig)) return array();
		list($defaultConfig, $mergeTags) = $this->getMergeTags($defaultConfig);
		
		if (!$appConfig || !is_array($appConfig)) return $defaultConfig;
		list($appConfig) = $this->getMergeTags($appConfig);
		
		foreach ($defaultConfig as $key => $value) {
			if (in_array($key, $mergeTags) && isset($appConfig[$key])) {
				$defaultConfig[$key] = array_merge((array)$defaultConfig[$key], (array)$appConfig[$key]);
				continue;
			}
			if (isset($appConfig[$key])) {
				$defaultConfig[$key] = $appConfig[$key];
				continue;
			}
		}

		if (!($difKeys = array_diff(array_keys($appConfig), array_keys($defaultConfig)))) return $defaultConfig;
		foreach($difKeys as $key) {
			$defaultConfig[$key] = $appConfig[$key];
		}
		return $defaultConfig;
	}
	
	/**
	 * 获得isMerge属性的标签，同时将该属性删除
	 * 
	 * @param array $parames
	 * @return array (处理后的数组，含有merge属性的标签集合)
	 */
	private function getMergeTags($params) {
		$mergeTags = array();
		foreach ($params as $key => $value) {
			if (is_array($value) && isset($value[self::ISMERGE])) {
				($this->checkCanMerge($value[self::ISMERGE])) && $mergeTags[] = $key;
				unset($params[$key][self::ISMERGE]);
			}
		}
		return array($params, $mergeTags);
	}
	
	/**
	 * 返回是否需要执行解析
	 * 
	 * 如果是debug模式，则返回false, 进行每次都进行解析
	 * 如果不是debug模式，则先判断是否设置了缓存模式
	 * 如果没有设置缓存则返回false, 进行解析，
	 * 如果设置了缓存模式，则判断缓存文件是否存在
	 * 如果该解析出来的文件不存在，则返回false, 执行解析
	 * 否则返回true, 直接读取缓存
	 * 
	 * @param string $cacheFile
	 * @return boolean  false:需要进行解析， true：不需要进行解析，直接读取缓存文件
	 */
	private function isCompiled($cacheFile) {
		if (IS_DEBUG || !W::ifCompile() || !is_file($this->buildCacheFilePath($cacheFile))) 
		    return false;
		return true;
	}
	
	/**
	 * 获得文件的后缀，决定采用的是哪种配置格式，
	 * 如果传递的文件配置格式不在支持范围内，则抛出异常
	 * 
	 * @return boolean : true  解析文件格式成功，解析失败则抛出异常
	 */
	private function getConfigFormat($configPath) {
		if ($configPath === '' )
			return self::CONFIG_XML;
		$format = strtoupper(trim(strrchr($configPath, '.'), '.'));
		if (!in_array($format, $this->getConfigFormatList())) {
			throw new WindException("The format of the config file doesn't sopported yet!");
		}
		return $format;
	}
	
	/**
	 * 判断数值是否在IsMerge的可用范围内
	 * @param string  $value 需要判断的字串
	 * @return boolean 
	 */
	private function checkCanMerge($value) {
		return in_array(strtolower(trim($value)), $this->mergeValue);
	}
	
	
	/**
	 * 保存成文件
	 * 
	 * @param string $filename
	 * @param array $data
	 * @return boolean 保存成功则返回true,保存失败则返回false
	 */
	private function saveConfigFile($filename, $data) {
		if (!W::ifCompile() || !$filename || !$data) return false;
		L::import('WIND:component.Common');
		return Common::saveData($this->buildCacheFilePath($filename), $data);
	}
	
	/**
	 * 获得默认配置文件的路径
	 * @return string 获得默认配置文件的路径
	 */
	private function getWindConfigPath() {
		return WIND_PATH . D_S . $this->windConfig . '.' . $this->parseFormat;
	}
	
	/**
	 * 构造文件的路径
	 * 
	 * @param string $fileName
	 * @return string 返回缓存文件的$fileName的绝对路径
	 */
    private function buildCacheFilePath($fileName) {
   		return COMPILE_PATH . D_S . strtolower($fileName);
    }
    
	/**
	 * 获得支持解析的配置文件格式的白名单
	 * @return array
	 */
	private function getConfigFormatList() {
		return array(self::CONFIG_XML, self::CONFIG_PHP, self::CONFIG_INI, self::CONFIG_PROPERTIES);
	}
}