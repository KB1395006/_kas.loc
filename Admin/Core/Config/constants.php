<?php

/**
 * NS
*/
use \Core\Classes\ConfigManager\ConfigManager as ini;

/**
 * Main configurations
*/
define('PROTOCOL', 'HTTP');

/**
 * Constants
*/
define('KAS_VERSION', ini::set('KAS_VERSION', '3.4.1'));
define('KAS_LAST_MOD', ini::set('KAS_LAST_MOD', '22.05.2016'));
define('KAS_CMS_PATH', '/Admin/_public/index.php');
define('KAS_PROJ_PATH', '/_public/index.php');
define('KAS_DATE_FORMAT', "Y-m-d H:i:s");
define('KAS_TPL_EXT', '.tpl');
define('KAS_CMD', 'CMD:');
define('KAS_CMS', 'CMS');
define('KAS_APP', 'APP');

define('ADMIN', 'admin');
define('ENCODING', 'UTF-8');
define('INDEX', 'index');
define('DS', '/');
define('ACT', 'action');
define('AC_TYPE', 'actionType');
define('SAFE', 'safe');
define('INS', 'insert');
define('DEL', 'delete');
define('DATA', 'data');
define('SN', 'synchronization');
define('COUNT', 'count');
define('CNT', 'COUNT');
define('CART', 'cart');
define('EXT', 'KAS_EXT');

define('CMS_TPL', 'Tpl/CMS/');
define('APP_TPL', 'Tpl/APP/');
define('P_TPL', 'parent.tpl');
define('C_TPL', 'child.tpl');

define('SMTP_TR', 'smtp_transport');
define('SMTP_FROM', 'smtp_from');
define('SMTP_HOST', 'smtp_host');
define('SMTP_PORT', 'smtp_port');
define('SMTP_USR', 'smtp_username');
define('SMTP_PSW', 'smtp_password');
define('SMTP_REAL_NAME', 'smtp_real_name');

// SMTP PARAMS
define('SMTP_TR_VAL', 'tcp');
define('SMTP_FROM_VAL', 'info@gromko.by');
define('SMTP_HOST_VAL', 'mail.gromko.by');
define('SMTP_PORT_VAL', false);
define('SMTP_USR_VAL', 'info@gromko.by');
define('SMTP_PSW_VAL', 'qwerty123');
define('SMTP_REAL_NAME_VAL', 'Gromko');
define('SMTP_SEND_TIMEOUT', 2);

/**
 * Регулярное выражение для поиска кода поставщика.
*/
define('VC_REG_EXP', '/[0-9]{2,}/');

/**
 * SQL params
*/
define('TABLE', 'table');
define('COLUMNS', 'columns');
define('COL', 'column');
define('CONDITION', 'condition');
define('CONDITION_ARR', 'conditionArr');
define('QUERY', 'query');
define('SQL_DATA', 'sqlData');


/**
 * Encryption
*/
define('KAS_DATA_ENCRYPTION_KEY', ini::set('KAS_DATA_ENCRYPTION_KEY', 'root'));
/**
 * CMS Encoding
*/
define('KAS_ENCODING', 'UTF-8');

/**
 * DB
*/
define('KAS_DB_HOST', ini::set('KAS_DB_HOST', 'localhost'));
define('KAS_DB_NAME', ini::set('KAS_DB_NAME', 'KAS_DB'));
define('KAS_DB_USER', ini::set('KAS_DB_USER', 'KAS_USER'));
define('KAS_DB_PASS', ini::set('KAS_DB_PASS', 'KAS_PASS'));
define('KAS_DB_DEFAULT_USER', ini::set('KAS_DB_DEFAULT_USER', 'root'));
define('KAS_DB_DEFAULT_PASS', ini::set('KAS_DB_DEFAULT_PASS', false));

/**
 * Rexp and Delimiters.
*/
define('KAS_LINE_DELIM', "\r\n");
define('KAS_TPL_MASK', '/%[\s]{0,}[A-Z0-9_]+[\s]{0,}\%/');

/**
 * Pathes and files
*/
define('KAS_EXT_PATH', 'Tpl/Extensions/');
define('KAS_EXT_FILE', 'Logs/Extensions.log');
define('KAS_TPL_DIR', 'Tpl/');
define('KAS_CONFIG_PATH', 'Config/');
define('KAS_CONFIG_FILE', 'config.ini');
define('KAS_ROUTING_FILE', 'routing.txt');
define('KAS_SITE_TEXT_FILE', 'sitetext.txt');
define('KAS_EXT_TEXT_FILE', 'extensions.txt');

/**
 * Константы библиотеки \kas
*/
define('KAS_SCAN_DIR',  1);
define('KAS_SCAN_FILE', 2);
define('KAS_SCAN_IMG',  3);
define('KAS_SCAN_DOC',  4);
define('KAS_SCAN_ALL',  5);
define('KAS_SCAN_TPL',  6);
define('KAS_SCAN_EXT',  7);

/**
*/
define('KAS_TMP_CLOSURE_DATA', 'TMP_CLOSURE_DATA');

/*SQL*/
define('KAS_SQL_Q1', 1);
define('KAS_SQL_Q2', 2);
define('KAS_SQL_Q3', 3);
define('KAS_SQL_Q4', 4);
define('KAS_SQL_Q5', 5);
define('KAS_SQL_Q6', 6);

/**MYSQL USAGE*/
define('CATEGORIES', 'KAS_CATEGORIES');
define('OFFERS', 'KAS_OFFERS');
define('PUB', 'KAS_PUBLICATIONS');
define('MEDIA', 'KAS_MEDIA');



define('TITLE', 'KAS_TITLE');
define('NAME', 'KAS_NAME');

define('DESC_S', 'KAS_SHORT_DESCRIPTION');
define('DESC_M', 'KAS_MIDDLE_DESCRIPTION');
define('DESC_L', 'KAS_LARGE_DESCRIPTION');

define('DATE', 'KAS_DATE');
define('TIME', 'KAS_TIME');
define('TYPE', 'KAS_TYPE');
define('KAS_TYPE', 'type');
define('FILT', 'KAS_FILTERS');
define('NAV', 'NAVIGATION');
define('LOC', 'location');
define('SEARCH', 'search');

/**Идентификаторы*/
define('ID',  'KAS_ID');                 // Идентификатор элемента
define('PID', 'KAS_PID');               // Идентификатор родителя
define('FID', 'KAS_FID');               // Идентификатор фильтра
define('GID', 'KAS_GID');               // Идентификатор группы
define('MKP', 'KAS_MARKUP');            // Наценка

// bug: \xD0\xA1
define('CID', 'KAS_CID');               // Идентификатор категории

define('CODE', 'KAS_CODE');             // Артикул
define('VC', 'KAS_VENDOR_CODE');        // Код поставщика

define('CUST_ID', 'KAS_CUSTOMER_ID');   // Идентификатор поставщика
define('LVL', 'KAS_LVL');               // Идентификатор поставщика

define('C_NAME', 'KAS_CATEGORY_NAME');  // Имя родительской категории

/**IMG*/
define('IMG_I', 'KAS_IMG_ICON');
define('IMG_S', 'KAS_IMG_SMALL');
define('IMG_M', 'KAS_IMG_MIDDLE');
define('IMG_L', 'KAS_IMG_LARGE');
define('IMG_G', 'KAS_IMG_GALLERY');

define('UPL', 'upload');

define('PRC', 'KAS_PRICE');
define('CUR_ID', 'KAS_CUR_ID');
define('CUR_V', 'KAS_CUR_VALUE');
define('MODEL', 'KAS_MODEL');
define('URL_SET', 'KAS_URL_SET');
define('STATUS', 'KAS_STATUS');
define('URI', 'KAS_URI');
define('SRC', 'KAS_SRC');
define('MIME', 'KAS_MIME');
define('PATH', 'KAS_PATH');

define('NAMESPACES', 'KAS_NAMESPACES');
define('NS_ID', 'KAS_NAMESPACE_ID');
define('NS_GET', 'KAS_GET_NAMESPACE');
define('NS_SET', 'KAS_SET_NAMESPACE');
define('NS_DEL', 'KAS_DELETE_NAMESPACE');
define('NS_INS', 'KAS_INSERT_NAMESPACE');
define('NS_UPD', 'KAS_UPDATE_NAMESPACE');

/**HTML TPL CONST*/
define('HTML_I', 'INDEX');
define('HTML_H', 'HEADER');
define('HTML_M', 'META');
define('HTML_F', 'FOOTER');
define('HTML_C', 'CONTENT');
define('HTML_T', 'DOC_TITLE');

define('TPL_EXT', '.tpl');
define('TPL_E', 'tpl');
define('TPL', 'KAS_TPL');

define('DOC_PATH', 'DOCUMENT_PATH');
define('HTTP_PATH', 'HTTP_PATH');
define('CONTENT', 'CONTENT');
define('TAB', 'tables');
define('TASK', 'task');
/**MYSQL COLUMNS*/

define('TERMINAL', 'terminal');
define('CMD', 'command');

define('IMG_PATH', 'upload/img/');
define('FLS_PATH', 'upload/files/');

/**Ajax*/
define('AJAX_LAST_REQUEST', 'ajaxLastRequest');
define('AJAX_CURRENT_REQUEST', 'ajaxCurrentRequest');
define('AJAX_REQUEST_TIMEOUT', 1000);

define('USD', 'usd');
define('EUR', 'eur');
define('RUR', 'rur');

define('WWW_PATH', '../../www/');
define('UPL_PATH', '../../www/uploads/');
define('DUMP_PATH', '../../www/dump/');
define('UPL_DIR', 'UPLOAD_DIR');

define('FM', 'KAS_FILE_MANAGER');

// DM types list
define('TYPE_IMG', 1);

// Image compression requirements
define('CC_IMG_LRG_RES', 1000);
define('CC_IMG_MID_RES', 500);
define('CC_IMG_ICO_RES', 200);

define('COMPRESSION_DIR', ['resize', 'l', 'm', 's']);
define('ATTR', ['KAS_WIDTH', 'KAS_HEIGHT']);


/**LOCATIONS*/
define('L1', '/catalog/');
define('L2', '/offers/');
define('L3', '/media/');
define('L4', '/cart/');
define('L5', '/order/');
define('L6', '/interface-text/');
define('L7', '/publications/');
define('L8', '/tpl/');