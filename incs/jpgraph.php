<?php
//=======================================================================
// File:	JPGRAPH.PHP
// Description:	PHP Graph Plotting library. Base module.
// Created: 	2001-01-08
// Ver:		$Id: jpgraph.php 1002 2008-06-09 20:24:37Z ljp $
//
// Copyright (c) Aditus Consulting. All rights reserved.
//========================================================================

require_once('jpg-config.inc.php');
require_once('jpgraph_gradient.php');
require_once('jpgraph_errhandler.inc.php');
require_once('jpgraph_ttf.inc.php');

// Version info
DEFINE('JPG_VERSION','2.3.3');

// Minimum required PHP version
DEFINE('MIN_PHPVERSION','5.1.0');

// Should the image be a truecolor image? 
DEFINE('USE_TRUECOLOR',true);

//------------------------------------------------------------------------
// Automatic settings of path for cache and font directory
// if they have not been previously specified
//------------------------------------------------------------------------
if(USE_CACHE) {
    if (!defined('CACHE_DIR')) {
	if ( strstr( PHP_OS, 'WIN') ) {
	    if( empty($_SERVER['TEMP']) ) {
		$t = new ErrMsgText();
		$msg = $t->Get(11,$file,$lineno);
		die($msg);
	    }
	    else {
		DEFINE('CACHE_DIR', $_SERVER['TEMP'] . '/');
	    }
	} else {
	    DEFINE('CACHE_DIR','/tmp/jpgraph_cache/');
	}
    }
}
elseif( !defined('CACHE_DIR') ) {
    DEFINE('CACHE_DIR', '');
}

if (!defined('TTF_DIR')) {
    if (strstr( PHP_OS, 'WIN') ) {
	$sroot = getenv('SystemRoot');
        if( empty($sroot) ) {
	    $t = new ErrMsgText();
	    $msg = $t->Get(12,$file,$lineno);
	    die($msg);
        }
	else {
	  DEFINE('TTF_DIR', $sroot.'/fonts/');
        }
    } else {
	DEFINE('TTF_DIR','/usr/X11R6/lib/X11/fonts/truetype/');
    }
}

if (!defined('MBTTF_DIR')) {
    DEFINE('MBTTF_DIR','/usr/share/fonts/ja/TrueType/');
}

//------------------------------------------------------------------
// Constants which are used as parameters for the method calls
//------------------------------------------------------------------


// Tick density
DEFINE("TICKD_DENSE",1);
DEFINE("TICKD_NORMAL",2);
DEFINE("TICKD_SPARSE",3);
DEFINE("TICKD_VERYSPARSE",4);

// Side for ticks and labels. 
DEFINE("SIDE_LEFT",-1);
DEFINE("SIDE_RIGHT",1);
DEFINE("SIDE_DOWN",-1);
DEFINE("SIDE_BOTTOM",-1);
DEFINE("SIDE_UP",1);
DEFINE("SIDE_TOP",1);

// Legend type stacked vertical or horizontal
DEFINE("LEGEND_VERT",0);
DEFINE("LEGEND_HOR",1);

// Mark types for plot marks
DEFINE("MARK_SQUARE",1);
DEFINE("MARK_UTRIANGLE",2);
DEFINE("MARK_DTRIANGLE",3);
DEFINE("MARK_DIAMOND",4);
DEFINE("MARK_CIRCLE",5);
DEFINE("MARK_FILLEDCIRCLE",6);
DEFINE("MARK_CROSS",7);
DEFINE("MARK_STAR",8);
DEFINE("MARK_X",9);
DEFINE("MARK_LEFTTRIANGLE",10);
DEFINE("MARK_RIGHTTRIANGLE",11);
DEFINE("MARK_FLASH",12);
DEFINE("MARK_IMG",13);
DEFINE("MARK_FLAG1",14);
DEFINE("MARK_FLAG2",15);
DEFINE("MARK_FLAG3",16);
DEFINE("MARK_FLAG4",17);

// Builtin images
DEFINE("MARK_IMG_PUSHPIN",50);
DEFINE("MARK_IMG_SPUSHPIN",50);
DEFINE("MARK_IMG_LPUSHPIN",51);
DEFINE("MARK_IMG_DIAMOND",52);
DEFINE("MARK_IMG_SQUARE",53);
DEFINE("MARK_IMG_STAR",54);
DEFINE("MARK_IMG_BALL",55);
DEFINE("MARK_IMG_SBALL",55);
DEFINE("MARK_IMG_MBALL",56);
DEFINE("MARK_IMG_LBALL",57);
DEFINE("MARK_IMG_BEVEL",58);

// Inline defines
DEFINE("INLINE_YES",1);
DEFINE("INLINE_NO",0);

// Format for background images
DEFINE("BGIMG_FILLPLOT",1);
DEFINE("BGIMG_FILLFRAME",2);
DEFINE("BGIMG_COPY",3);
DEFINE("BGIMG_CENTER",4);

// Depth of objects
DEFINE("DEPTH_BACK",0);
DEFINE("DEPTH_FRONT",1);

// Direction
DEFINE("VERTICAL",1);
DEFINE("HORIZONTAL",0);


// Axis styles for scientific style axis
DEFINE('AXSTYLE_SIMPLE',1);
DEFINE('AXSTYLE_BOXIN',2);
DEFINE('AXSTYLE_BOXOUT',3);
DEFINE('AXSTYLE_YBOXIN',4);
DEFINE('AXSTYLE_YBOXOUT',5);

// Style for title backgrounds
DEFINE('TITLEBKG_STYLE1',1);
DEFINE('TITLEBKG_STYLE2',2);
DEFINE('TITLEBKG_STYLE3',3);
DEFINE('TITLEBKG_FRAME_NONE',0);
DEFINE('TITLEBKG_FRAME_FULL',1);
DEFINE('TITLEBKG_FRAME_BOTTOM',2);
DEFINE('TITLEBKG_FRAME_BEVEL',3);
DEFINE('TITLEBKG_FILLSTYLE_HSTRIPED',1);
DEFINE('TITLEBKG_FILLSTYLE_VSTRIPED',2);
DEFINE('TITLEBKG_FILLSTYLE_SOLID',3);

// Style for background gradient fills
DEFINE('BGRAD_FRAME',1);
DEFINE('BGRAD_MARGIN',2);
DEFINE('BGRAD_PLOT',3);

// Width of tab titles
DEFINE('TABTITLE_WIDTHFIT',0);
DEFINE('TABTITLE_WIDTHFULL',-1);

// Defines for 3D skew directions
DEFINE('SKEW3D_UP',0);
DEFINE('SKEW3D_DOWN',1);
DEFINE('SKEW3D_LEFT',2);
DEFINE('SKEW3D_RIGHT',3);

// Line styles
DEFINE('LINESTYLE_SOLID',1);
DEFINE('LINESTYLE_DOTTED',2);
DEFINE('LINESTYLE_DASHED',3);
DEFINE('LINESTYLE_LONGDASH',4);

// For internal use only
DEFINE("_JPG_DEBUG",false);
DEFINE("_FORCE_IMGTOFILE",false);
DEFINE("_FORCE_IMGDIR",'/tmp/jpgimg/');

require_once('gd_image.inc.php');

/**
 * CheckPHPVersion
 * Insert description here
 *
 * @param $aMinVersion
 *
 * @return
 *
 * @access
 * @static
 * @see
 * @since
 */
function CheckPHPVersion($aMinVersion)
{
    list($majorC, $minorC, $editC) = split('[/.-]', PHP_VERSION);
    list($majorR, $minorR, $editR) = split('[/.-]', $aMinVersion);
  
    if ($majorC != $majorR) return false;
    if ($majorC < $majorR) return false;
    // same major - check ninor
    if ($minorC > $minorR) return true;
    if ($minorC < $minorR) return false;
    // and same minor
    if ($editC  >= $editR)  return true;
    return true;
}

//
// Make sure PHP version is high enough
//
if( !CheckPHPVersion(MIN_PHPVERSION) ) {
    JpGraphError::RaiseL(13,PHP_VERSION,MIN_PHPVERSION);
    die();
}


//
// Make GD sanity check
//
if( !function_exists("imagetypes") || !function_exists('imagecreatefromstring') ) {
    JpGraphError::RaiseL(25001);
//("This PHP installation is not configured with the GD library. Please recompile PHP with GD support to run JpGraph. (Neither function imagetypes() nor imagecreatefromstring() does exist)");
}

//
// Setup PHP error handler
//
/**
 * _phpErrorHandler
 * Insert description here
 *
 * @param $errno
 * @param $errmsg
 * @param $filename
 * @param $linenum
 * @param $vars
 *
 * @return
 *
 * @access
 * @static
 * @see
 * @since
 */
function _phpErrorHandler($errno,$errmsg,$filename, $linenum, $vars) {
    // Respect current error level
    if( $errno & error_reporting() ) {
	JpGraphError::RaiseL(25003,basename($filename),$linenum,$errmsg); 
    }
}

if( INSTALL_PHP_ERR_HANDLER ) {
    set_error_handler("_phpErrorHandler");
}

//
//Check if there were any warnings, perhaps some wrong includes by the
//user
//
if( isset($GLOBALS['php_errormsg']) && CATCH_PHPERRMSG && 
    !preg_match('/|Deprecated|/i', $GLOBALS['php_errormsg']) ) {
    JpGraphError::RaiseL(25004,$GLOBALS['php_errormsg']);
}


// Useful mathematical function
/**
 * sign
 * Insert description here
 *
 * @param $a
 *
 * @return
 *
 * @access
 * @static
 * @see
 * @since
 */
function sign($a) {return $a >= 0 ? 1 : -1;}

// Utility function to generate an image name based on the filename we
// are running from and assuming we use auto detection of graphic format
// (top level), i.e it is safe to call this function
// from a script that uses JpGraph
/**
 * GenImgName
 * Insert description here
 *
 *
 * @return
 *
 * @access
 * @static
 * @see
 * @since
 */
function GenImgName() {
    // Determine what format we should use when we save the images
    $supported = imagetypes();
    if( $supported & IMG_PNG )	   $img_format="png";
    elseif( $supported & IMG_GIF ) $img_format="gif";
    elseif( $supported & IMG_JPG ) $img_format="jpeg";
    elseif( $supported & IMG_WBMP ) $img_format="wbmp";
    elseif( $supported & IMG_XPM ) $img_format="xpm";


    if( !isset($_SERVER['PHP_SELF']) )
	JpGraphError::RaiseL(25005);
//(" Can't access PHP_SELF, PHP global variable. You can't run PHP from command line if you want to use the 'auto' naming of cache or image files.");
    $fname = basename($_SERVER['PHP_SELF']);
    if( !empty($_SERVER['QUERY_STRING']) ) {
	$q = @$_SERVER['QUERY_STRING'];
	$fname .= '_'.preg_replace("/\W/", "_", $q).'.'.$img_format;
    }
    else {
	$fname = substr($fname,0,strlen($fname)-4).'.'.$img_format;
    }
    return $fname;
}


//===================================================
// CLASS JpgTimer
// Description: General timing utility class to handle
// time measurement of generating graphs. Multiple
// timers can be started.
//===================================================
/**
 * JpgTimer
 * Insert description here
 *
 * @category
 * @package
 * @author
 * @copyright
 * @license
 * @version
 * @link
 * @see
 * @since
 */
class JpgTimer {
    private $start, $idx;	
//---------------
// CONSTRUCTOR
    /**
     * JpgTimer
     * Insert description here
     *
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function JpgTimer() {
	$this->idx=0;
    }

//---------------
// PUBLIC METHODS	

    // Push a new timer start on stack
    /**
     * Push
     * Insert description here
     *
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function Push() {
	list($ms,$s)=explode(" ",microtime());	
	$this->start[$this->idx++]=floor($ms*1000) + 1000*$s;	
    }

    // Pop the latest timer start and return the diff with the
    // current time
    /**
     * Pop
     * Insert description here
     *
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function Pop() {
	assert($this->idx>0);
	list($ms,$s)=explode(" ",microtime());	
	$etime=floor($ms*1000) + (1000*$s);
	$this->idx--;
	return $etime-$this->start[$this->idx];
    }
} // Class

$gJpgBrandTiming = BRAND_TIMING;
//===================================================
// CLASS DateLocale
// Description: Hold localized text used in dates
//===================================================
/**
 * DateLocale
 * Insert description here
 *
 * @category
 * @package
 * @author
 * @copyright
 * @license
 * @version
 * @link
 * @see
 * @since
 */
class DateLocale {
 
    public $iLocale = 'C'; // environmental locale be used by default
    private $iDayAbb = null, $iShortDay = null, $iShortMonth = null, $iMonthName = null;

//---------------
// CONSTRUCTOR	
    /**
     * DateLocale
     * Insert description here
     *
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function DateLocale() {
	settype($this->iDayAbb, 'array');
	settype($this->iShortDay, 'array');
	settype($this->iShortMonth, 'array');
	settype($this->iMonthName, 'array');


	$this->Set('C');
    }

//---------------
// PUBLIC METHODS	
    /**
     * Set
     * Insert description here
     *
     * @param $aLocale
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function Set($aLocale) {
	if ( in_array($aLocale, array_keys($this->iDayAbb)) ){ 
	    $this->iLocale = $aLocale;
	    return TRUE;  // already cached nothing else to do!
	}

	$pLocale = setlocale(LC_TIME, 0); // get current locale for LC_TIME

	if (is_array($aLocale)) {
	    foreach ($aLocale as $loc) {
		$res = @setlocale(LC_TIME, $loc);
		if ( $res ) {
		    $aLocale = $loc;
		    break;
		}
	    }
	}
	else {
	    $res = @setlocale(LC_TIME, $aLocale);
	}

	if ( ! $res ){
	    JpGraphError::RaiseL(25007,$aLocale);
//("You are trying to use the locale ($aLocale) which your PHP installation does not support. Hint: Use '' to indicate the default locale for this geographic region.");
	    return FALSE;
	}
 
	$this->iLocale = $aLocale;
	for ( $i = 0, $ofs = 0 - strftime('%w'); $i < 7; $i++, $ofs++ ){
	    $day = strftime('%a', strtotime("$ofs day"));
	    $day[0] = strtoupper($day[0]);
	    $this->iDayAbb[$aLocale][]= $day[0];
	    $this->iShortDay[$aLocale][]= $day;
	}

	for($i=1; $i<=12; ++$i) {
	    list($short ,$full) = explode('|', strftime("%b|%B",strtotime("2001-$i-01")));
	    $this->iShortMonth[$aLocale][] = ucfirst($short);
	    $this->iMonthName [$aLocale][] = ucfirst($full);
	}
		
	setlocale(LC_TIME, $pLocale);

	return TRUE;
    }


    /**
     * GetDayAbb
     * Insert description here
     *
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function GetDayAbb() {
	return $this->iDayAbb[$this->iLocale];
    }
	
    /**
     * GetShortDay
     * Insert description here
     *
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function GetShortDay() {
	return $this->iShortDay[$this->iLocale];
    }

    /**
     * GetShortMonth
     * Insert description here
     *
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function GetShortMonth() {
	return $this->iShortMonth[$this->iLocale];
    }
	
    /**
     * GetShortMonthName
     * Insert description here
     *
     * @param $aNbr
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function GetShortMonthName($aNbr) {
	return $this->iShortMonth[$this->iLocale][$aNbr];
    }

    /**
     * GetLongMonthName
     * Insert description here
     *
     * @param $aNbr
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function GetLongMonthName($aNbr) {
	return $this->iMonthName[$this->iLocale][$aNbr];
    }

    /**
     * GetMonth
     * Insert description here
     *
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function GetMonth() {
	return $this->iMonthName[$this->iLocale];
    }
}

$gDateLocale = new DateLocale();
$gJpgDateLocale = new DateLocale();

//=======================================================
// CLASS Footer
// Description: Encapsulates the footer line in the Graph
//=======================================================
/**
 * Footer
 * Insert description here
 *
 * @category
 * @package
 * @author
 * @copyright
 * @license
 * @version
 * @link
 * @see
 * @since
 */
class Footer {
    public $iLeftMargin = 3, $iRightMargin = 3, $iBottomMargin = 3 ;
    public $left,$center,$right;

    /**
     * Footer
     * Insert description here
     *
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function Footer() {
	$this->left = new Text();
	$this->left->ParagraphAlign('left');
	$this->center = new Text();
	$this->center->ParagraphAlign('center');
	$this->right = new Text();
	$this->right->ParagraphAlign('right');
    }

    /**
     * SetMargin
     * Insert description here
     *
     * @param $aLeft
     * @param $aRight
     * @param $aBottom
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetMargin($aLeft=3,$aRight=3,$aBottom=3) {
	$this->iLeftMargin = $aLeft;
	$this->iRightMargin = $aRight;
	$this->iBottomMargin = $aBottom;
    }

    /**
     * Stroke
     * Insert description here
     *
     * @param $aImg
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function Stroke($aImg) {
	$y = $aImg->height - $this->iBottomMargin;
	$x = $this->iLeftMargin;
	$this->left->Align('left','bottom');
	$this->left->Stroke($aImg,$x,$y);

	$x = ($aImg->width - $this->iLeftMargin - $this->iRightMargin)/2;
	$this->center->Align('center','bottom');
	$this->center->Stroke($aImg,$x,$y);

	$x = $aImg->width - $this->iRightMargin;
	$this->right->Align('right','bottom');
	$this->right->Stroke($aImg,$x,$y);
    }
}


//===================================================
// CLASS Graph
// Description: Main class to handle graphs
//===================================================
/**
 * Graph
 * Insert description here
 *
 * @category
 * @package
 * @author
 * @copyright
 * @license
 * @version
 * @link
 * @see
 * @since
 */
class Graph {
    public $cache=null;		// Cache object (singleton)
    public $img=null;			// Img object (singleton)
    public $plots=array();	// Array of all plot object in the graph (for Y 1 axis)
    public $y2plots=array();// Array of all plot object in the graph (for Y 2 axis)
    public $ynplots=array();
    public $xscale=null;		// X Scale object (could be instance of LinearScale or LogScale
    public $yscale=null,$y2scale=null, $ynscale=array();
    public $iIcons = array();      // Array of Icons to add to 
    public $cache_name;		// File name to be used for the current graph in the cache directory
    public $xgrid=null;		// X Grid object (linear or logarithmic)
    public $ygrid=null,$y2grid=null; //dito for Y
    public $doframe=true,$frame_color=array(0,0,0), $frame_weight=1;	// Frame around graph
    public $boxed=false, $box_color=array(0,0,0), $box_weight=1;		// Box around plot area
    public $doshadow=false,$shadow_width=4,$shadow_color=array(102,102,102);	// Shadow for graph
    public $xaxis=null;		// X-axis (instane of Axis class)
    public $yaxis=null, $y2axis=null, $ynaxis=array();	// Y axis (instance of Axis class)
    public $margin_color=array(200,200,200);	// Margin color of graph
    public $plotarea_color=array(255,255,255);	// Plot area color
    public $title,$subtitle,$subsubtitle; 	// Title and subtitle(s) text object
    public $axtype="linlin";	// Type of axis
    public $xtick_factor,$ytick_factor;	// Factor to determine the maximum number of ticks depending on the plot width
    public $texts=null, $y2texts=null;		// Text object to ge shown in the graph
    public $lines=null, $y2lines=null;
    public $bands=null, $y2bands=null;
    public $text_scale_off=0, $text_scale_abscenteroff=-1;	// Text scale in fractions and for centering bars
    public $background_image="",$background_image_type=-1,$background_image_format="png";
    public $background_image_bright=0,$background_image_contr=0,$background_image_sat=0;
    public $image_bright=0, $image_contr=0, $image_sat=0;
    public $inline;
    public $showcsim=0,$csimcolor="red"; //debug stuff, draw the csim boundaris on the image if <>0
    public $grid_depth=DEPTH_BACK;	// Draw grid under all plots as default
    public $iAxisStyle = AXSTYLE_SIMPLE;
    public $iCSIMdisplay=false,$iHasStroked = false;
    public $footer;
    public $csimcachename = '', $csimcachetimeout = 0, $iCSIMImgAlt='';
    public $iDoClipping = false;
    public $y2orderback=true;
    public $tabtitle;
    public $bkg_gradtype=-1,$bkg_gradstyle=BGRAD_MARGIN;
    public $bkg_gradfrom='navy', $bkg_gradto='silver';
    public $titlebackground = false;
    public $titlebackground_color = 'lightblue',
	$titlebackground_style = 1,
	$titlebackground_framecolor = 'blue',
	$titlebackground_framestyle = 2,
	$titlebackground_frameweight = 1,
	$titlebackground_bevelheight = 3 ;
    public $titlebkg_fillstyle=TITLEBKG_FILLSTYLE_SOLID;
    public $titlebkg_scolor1='black',$titlebkg_scolor2='white';
    public $framebevel = false, $framebeveldepth = 2 ;
    public $framebevelborder = false, $framebevelbordercolor='black';
    public $framebevelcolor1='white@0.4', $framebevelcolor2='black@0.4';
    public $background_image_mix=100;
    public $background_cflag = '';
    public $background_cflag_type = BGIMG_FILLPLOT;
    public $background_cflag_mix = 100;
    public $iImgTrans=false,
	$iImgTransHorizon = 100,$iImgTransSkewDist=150,
	$iImgTransDirection = 1, $iImgTransMinSize = true,
	$iImgTransFillColor='white',$iImgTransHighQ=false,
	$iImgTransBorder=false,$iImgTransHorizonPos=0.5;
    public $legend;
    protected $iYAxisDeltaPos=50;
    protected $iIconDepth=DEPTH_BACK;
    protected $iAxisLblBgType = 0,
	$iXAxisLblBgFillColor = 'lightgray', $iXAxisLblBgColor = 'black',
	$iYAxisLblBgFillColor = 'lightgray', $iYAxisLblBgColor = 'black';
    protected $iTables=NULL;

//---------------
// CONSTRUCTOR

    // aWIdth 		Width in pixels of image
    // aHeight  	Height in pixels of image
    // aCachedName	Name for image file in cache directory 
    // aTimeOut		Timeout in minutes for image in cache
    // aInline		If true the image is streamed back in the call to Stroke()
    //			If false the image is just created in the cache
    /**
     * Graph
     * Insert description here
     *
     * @param $aWidth
     * @param $aHeight
     * @param $aCachedName
     * @param $aTimeOut
     * @param $aInline
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function Graph($aWidth=300,$aHeight=200,$aCachedName="",$aTimeOut=0,$aInline=true) {
	GLOBAL $gJpgBrandTiming;
	// If timing is used create a new timing object
	if( $gJpgBrandTiming ) {
	    global $tim;
	    $tim = new JpgTimer();
	    $tim->Push();
	}

	if( !is_numeric($aWidth) || !is_numeric($aHeight) ) {
	    JpGraphError::RaiseL(25008);//('Image width/height argument in Graph::Graph() must be numeric');
	}		

	// Automatically generate the image file name based on the name of the script that
	// generates the graph
	if( $aCachedName=="auto" )
	    $aCachedName=GenImgName();
			
	// Should the image be streamed back to the browser or only to the cache?
	$this->inline=$aInline;
		
	$this->img	= new RotImage($aWidth,$aHeight);

	$this->cache 	= new ImgStreamCache($this->img);
	$this->cache->SetTimeOut($aTimeOut);

	$this->title = new Text();
	$this->title->ParagraphAlign('center');
	$this->title->SetFont(FF_FONT2,FS_BOLD);
	$this->title->SetMargin(3);
	$this->title->SetAlign('center');

	$this->subtitle = new Text();
	$this->subtitle->ParagraphAlign('center');
	$this->subtitle->SetMargin(2);
	$this->subtitle->SetAlign('center');

	$this->subsubtitle = new Text();
	$this->subsubtitle->ParagraphAlign('center');
	$this->subsubtitle->SetMargin(2);
	$this->subsubtitle->SetAlign('center');

	$this->legend = new Legend();
	$this->footer = new Footer();

	// Window doesn't like '?' in the file name so replace it with an '_'
	$aCachedName = str_replace("?","_",$aCachedName);

	// If the cached version exist just read it directly from the
	// cache, stream it back to browser and exit
	if( $aCachedName!="" && READ_CACHE && $aInline )
	    if( $this->cache->GetAndStream($aCachedName) ) {
		exit();
	    }
				
	$this->cache_name = $aCachedName;
	$this->SetTickDensity(); // Normal density

	$this->tabtitle = new GraphTabTitle();
    }
//---------------
// PUBLIC METHODS	
    
    // Enable final image perspective transformation
    /**
     * Set3DPerspective
     * Insert description here
     *
     * @param $aDir
     * @param $aHorizon
     * @param $aSkewDist
     * @param $aQuality
     * @param $aFillColor
     * @param $aBorder
     * @param $aMinSize
     * @param $aHorizonPos
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function Set3DPerspective($aDir=1,$aHorizon=100,$aSkewDist=120,$aQuality=false,$aFillColor='#FFFFFF',$aBorder=false,$aMinSize=true,$aHorizonPos=0.5) {
	$this->iImgTrans = true;
	$this->iImgTransHorizon = $aHorizon;
	$this->iImgTransSkewDist= $aSkewDist;
	$this->iImgTransDirection = $aDir;
	$this->iImgTransMinSize = $aMinSize;
	$this->iImgTransFillColor=$aFillColor;
	$this->iImgTransHighQ=$aQuality;
	$this->iImgTransBorder=$aBorder;
	$this->iImgTransHorizonPos=$aHorizonPos;
    }

    // Set Image format and optional quality
    /**
     * SetImgFormat
     * Insert description here
     *
     * @param $aFormat
     * @param $aQuality
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetImgFormat($aFormat,$aQuality=75) {
	$this->img->SetImgFormat($aFormat,$aQuality);
    }

    // Should the grid be in front or back of the plot?
    /**
     * SetGridDepth
     * Insert description here
     *
     * @param $aDepth
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetGridDepth($aDepth) {
	$this->grid_depth=$aDepth;
    }

    /**
     * SetIconDepth
     * Insert description here
     *
     * @param $aDepth
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetIconDepth($aDepth) {
	$this->iIconDepth=$aDepth;
    }
	
    // Specify graph angle 0-360 degrees.
    /**
     * SetAngle
     * Insert description here
     *
     * @param $aAngle
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetAngle($aAngle) {
	$this->img->SetAngle($aAngle);
    }

    /**
     * SetAlphaBlending
     * Insert description here
     *
     * @param $aFlg
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetAlphaBlending($aFlg=true) {
	$this->img->SetAlphaBlending($aFlg);
    }

    // Shortcut to image margin
    /**
     * SetMargin
     * Insert description here
     *
     * @param $lm
     * @param $rm
     * @param $tm
     * @param $bm
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetMargin($lm,$rm,$tm,$bm) {
	$this->img->SetMargin($lm,$rm,$tm,$bm);
    }

    /**
     * SetY2OrderBack
     * Insert description here
     *
     * @param $aBack
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetY2OrderBack($aBack=true) {
	$this->y2orderback = $aBack;
    }

    // Rotate the graph 90 degrees and set the margin 
    // when we have done a 90 degree rotation
    /**
     * Set90AndMargin
     * Insert description here
     *
     * @param $lm
     * @param $rm
     * @param $tm
     * @param $bm
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function Set90AndMargin($lm=0,$rm=0,$tm=0,$bm=0) {
	$lm = $lm ==0 ? floor(0.2 * $this->img->width)  : $lm ;
	$rm = $rm ==0 ? floor(0.1 * $this->img->width)  : $rm ;
	$tm = $tm ==0 ? floor(0.2 * $this->img->height) : $tm ;
	$bm = $bm ==0 ? floor(0.1 * $this->img->height) : $bm ;

	$adj = ($this->img->height - $this->img->width)/2;
	$this->img->SetMargin($tm-$adj,$bm-$adj,$rm+$adj,$lm+$adj);
	$this->img->SetCenter(floor($this->img->width/2),floor($this->img->height/2));
	$this->SetAngle(90);
	if( empty($this->yaxis) || empty($this->xaxis) ) {
	    JpgraphError::RaiseL(25009);//('You must specify what scale to use with a call to Graph::SetScale()');
	}
	$this->xaxis->SetLabelAlign('right','center');
	$this->yaxis->SetLabelAlign('center','bottom');
    }
	
    /**
     * SetClipping
     * Insert description here
     *
     * @param $aFlg
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetClipping($aFlg=true) {
	$this->iDoClipping = $aFlg ;
    }

    // Add a plot object to the graph
    /**
     * Add
     * Insert description here
     *
     * @param $aPlot
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function Add($aPlot) {
	if( $aPlot == null )
	    JpGraphError::RaiseL(25010);//("Graph::Add() You tried to add a null plot to the graph.");
	if( is_array($aPlot) && count($aPlot) > 0 )
	    $cl = $aPlot[0];
	else
	    $cl = $aPlot;

	if( $cl instanceof Text ) 
	    $this->AddText($aPlot);
	elseif( $cl instanceof PlotLine )
	    $this->AddLine($aPlot);
	elseif( class_exists('PlotBand',false) && ($cl instanceof PlotBand) )
	    $this->AddBand($aPlot);
	elseif( class_exists('IconPlot',false) && ($cl instanceof IconPlot) )
	    $this->AddIcon($aPlot);
	elseif( class_exists('GTextTable',false) && ($cl instanceof GTextTable) )
	    $this->AddTable($aPlot);
	else
	    $this->plots[] = $aPlot;
    }

    /**
     * AddTable
     * Insert description here
     *
     * @param $aTable
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function AddTable($aTable) {
	if( is_array($aTable) ) {
	    for($i=0; $i < count($aTable); ++$i )
		$this->iTables[]=$aTable[$i];
	}
	else {
	    $this->iTables[] = $aTable ;
	}	
    }

    /**
     * AddIcon
     * Insert description here
     *
     * @param $aIcon
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function AddIcon($aIcon) {
	if( is_array($aIcon) ) {
	    for($i=0; $i < count($aIcon); ++$i )
		$this->iIcons[]=$aIcon[$i];
	}
	else {
	    $this->iIcons[] = $aIcon ;
	}	
    }

    // Add plot to second Y-scale
    /**
     * AddY2
     * Insert description here
     *
     * @param $aPlot
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function AddY2($aPlot) {
	if( $aPlot == null )
	    JpGraphError::RaiseL(25011);//("Graph::AddY2() You tried to add a null plot to the graph.");	

	if( is_array($aPlot) && count($aPlot) > 0 )
	    $cl = $aPlot[0];
	else
	    $cl = $aPlot;

	if( $cl instanceof Text ) 
	    $this->AddText($aPlot,true);
	elseif( $cl instanceof PlotLine )
	    $this->AddLine($aPlot,true);
	elseif( class_exists('PlotBand',false) && ($cl instanceof PlotBand) )
	    $this->AddBand($aPlot,true);
	else
	    $this->y2plots[] = $aPlot;
    }
	
    // Add plot to the extra Y-axes
    /**
     * AddY
     * Insert description here
     *
     * @param $aN
     * @param $aPlot
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function AddY($aN,$aPlot) {

	if( $aPlot == null )
	    JpGraphError::RaiseL(25012);//("Graph::AddYN() You tried to add a null plot to the graph.");	

	if( is_array($aPlot) && count($aPlot) > 0 )
	    $cl = $aPlot[0];
	else
	    $cl = $aPlot;

	if( ($cl instanceof Text) || ($cl instanceof PlotLine) || 
	    (class_exists('PlotBand',false) && ($cl instanceof PlotBand)) )
	    JpGraph::RaiseL(25013);//('You can only add standard plots to multiple Y-axis');
	else
	    $this->ynplots[$aN][] = $aPlot;
    }

    // Add text object to the graph
    /**
     * AddText
     * Insert description here
     *
     * @param $aTxt
     * @param $aToY2
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function AddText($aTxt,$aToY2=false) {
	if( $aTxt == null )
	    JpGraphError::RaiseL(25014);//("Graph::AddText() You tried to add a null text to the graph.");		
	if( $aToY2 ) {
	    if( is_array($aTxt) ) {
		for($i=0; $i < count($aTxt); ++$i )
		    $this->y2texts[]=$aTxt[$i];
	    }
	    else
		$this->y2texts[] = $aTxt;
	}
	else {
	    if( is_array($aTxt) ) {
		for($i=0; $i < count($aTxt); ++$i )
		    $this->texts[]=$aTxt[$i];
	    }
	    else
		$this->texts[] = $aTxt;
	}
    }
	
    // Add a line object (class PlotLine) to the graph
    /**
     * AddLine
     * Insert description here
     *
     * @param $aLine
     * @param $aToY2
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function AddLine($aLine,$aToY2=false) {
	if( $aLine == null )
	    JpGraphError::RaiseL(25015);//("Graph::AddLine() You tried to add a null line to the graph.");	

	if( $aToY2 ) {
 	    if( is_array($aLine) ) {
		for($i=0; $i < count($aLine); ++$i )
		    $this->y2lines[]=$aLine[$i];
	    }
	    else
		$this->y2lines[] = $aLine;
	}
	else {
 	    if( is_array($aLine) ) {
		for($i=0; $i<count($aLine); ++$i )
		    $this->lines[]=$aLine[$i];
	    }
	    else
		$this->lines[] = $aLine;
	}
    }

    // Add vertical or horizontal band
    /**
     * AddBand
     * Insert description here
     *
     * @param $aBand
     * @param $aToY2
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function AddBand($aBand,$aToY2=false) {
	if( $aBand == null )
	    JpGraphError::RaiseL(25016);//(" Graph::AddBand() You tried to add a null band to the graph.");

	if( $aToY2 ) {
	    if( is_array($aBand) ) {
		for($i=0; $i < count($aBand); ++$i )
		    $this->y2bands[] = $aBand[$i];
	    }
	    else
		$this->y2bands[] = $aBand;
	}
	else {
	    if( is_array($aBand) ) {
		for($i=0; $i < count($aBand); ++$i )
		    $this->bands[] = $aBand[$i];
	    }
	    else
		$this->bands[] = $aBand;
	}
    }

    /**
     * SetBackgroundGradient
     * Insert description here
     *
     * @param $aFrom
     * @param $aTo
     * @param $aGradType
     * @param $aStyle
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetBackgroundGradient($aFrom='navy',$aTo='silver',$aGradType=2,$aStyle=BGRAD_FRAME) {
	$this->bkg_gradtype=$aGradType;
	$this->bkg_gradstyle=$aStyle;
	$this->bkg_gradfrom = $aFrom;
	$this->bkg_gradto = $aTo;
    } 
	
    // Set a country flag in the background
    /**
     * SetBackgroundCFlag
     * Insert description here
     *
     * @param $aName
     * @param $aBgType
     * @param $aMix
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetBackgroundCFlag($aName,$aBgType=BGIMG_FILLPLOT,$aMix=100) {
	$this->background_cflag = $aName;
	$this->background_cflag_type = $aBgType;
	$this->background_cflag_mix = $aMix;
    }

    // Alias for the above method
    /**
     * SetBackgroundCountryFlag
     * Insert description here
     *
     * @param $aName
     * @param $aBgType
     * @param $aMix
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetBackgroundCountryFlag($aName,$aBgType=BGIMG_FILLPLOT,$aMix=100) {
	$this->background_cflag = $aName;
	$this->background_cflag_type = $aBgType;
	$this->background_cflag_mix = $aMix;
    }


    // Specify a background image
    /**
     * SetBackgroundImage
     * Insert description here
     *
     * @param $aFileName
     * @param $aBgType
     * @param $aImgFormat
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetBackgroundImage($aFileName,$aBgType=BGIMG_FILLPLOT,$aImgFormat="auto") {

	if( !USE_TRUECOLOR ) {
	    JpGraphError::RaiseL(25017);//("You are using GD 2.x and are trying to use a background images on a non truecolor image. To use background images with GD 2.x you <b>must</b> enable truecolor by setting the USE_TRUECOLOR constant to TRUE. Due to a bug in GD 2.0.1 using any truetype fonts with truecolor images will result in very poor quality fonts.");
	}

	// Get extension to determine image type
	if( $aImgFormat == "auto" ) {
	    $e = explode('.',$aFileName);
	    if( !$e ) {
		JpGraphError::RaiseL(25018,$aFileName);//('Incorrect file name for Graph::SetBackgroundImage() : '.$aFileName.' Must have a valid image extension (jpg,gif,png) when using autodetection of image type');
	    }

	    $valid_formats = array('png', 'jpg', 'gif');
	    $aImgFormat = strtolower($e[count($e)-1]);
	    if ($aImgFormat == 'jpeg')  {
		$aImgFormat = 'jpg';
	    }
	    elseif (!in_array($aImgFormat, $valid_formats) )  {
		JpGraphError::RaiseL(25019,$aImgFormat);//('Unknown file extension ($aImgFormat) in Graph::SetBackgroundImage() for filename: '.$aFileName);
	    }    
	}

	$this->background_image = $aFileName;
	$this->background_image_type=$aBgType;
	$this->background_image_format=$aImgFormat;
    }

    /**
     * SetBackgroundImageMix
     * Insert description here
     *
     * @param $aMix
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetBackgroundImageMix($aMix) {
	$this->background_image_mix = $aMix ;
    }
	
    // Specify axis style (boxed or single)
    /**
     * SetAxisStyle
     * Insert description here
     *
     * @param $aStyle
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetAxisStyle($aStyle) {
        $this->iAxisStyle = $aStyle ;
    }
	
    // Set a frame around the plot area
    /**
     * SetBox
     * Insert description here
     *
     * @param $aDrawPlotFrame
     * @param $aPlotFrameColor
     * @param 0
     * @param 0
     * @param 0
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetBox($aDrawPlotFrame=true,$aPlotFrameColor=array(0,0,0),$aPlotFrameWeight=1) {
	$this->boxed = $aDrawPlotFrame;
	$this->box_weight = $aPlotFrameWeight;
	$this->box_color = $aPlotFrameColor;
    }
	
    // Specify color for the plotarea (not the margins)
    /**
     * SetColor
     * Insert description here
     *
     * @param $aColor
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetColor($aColor) {
	$this->plotarea_color=$aColor;
    }
	
    // Specify color for the margins (all areas outside the plotarea)
    /**
     * SetMarginColor
     * Insert description here
     *
     * @param $aColor
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetMarginColor($aColor) {
	$this->margin_color=$aColor;
    }
	
    // Set a frame around the entire image
    /**
     * SetFrame
     * Insert description here
     *
     * @param $aDrawImgFrame
     * @param $aImgFrameColor
     * @param 0
     * @param 0
     * @param 0
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetFrame($aDrawImgFrame=true,$aImgFrameColor=array(0,0,0),$aImgFrameWeight=1) {
	$this->doframe = $aDrawImgFrame;
	$this->frame_color = $aImgFrameColor;
	$this->frame_weight = $aImgFrameWeight;
    }

    /**
     * SetFrameBevel
     * Insert description here
     *
     * @param $aDepth
     * @param $aBorder
     * @param $aBorderColor
     * @param $aColor1
     * @param $aColor2
     * @param $aFlg
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetFrameBevel($aDepth=3,$aBorder=false,$aBorderColor='black',$aColor1='white@0.4',$aColor2='darkgray@0.4',$aFlg=true) {
	$this->framebevel = $aFlg ;
	$this->framebeveldepth = $aDepth ;
	$this->framebevelborder = $aBorder ;
	$this->framebevelbordercolor = $aBorderColor ;
	$this->framebevelcolor1 = $aColor1 ;
	$this->framebevelcolor2 = $aColor2 ;

	$this->doshadow = false ;
    }

    // Set the shadow around the whole image
    /**
     * SetShadow
     * Insert description here
     *
     * @param $aShowShadow
     * @param $aShadowWidth
     * @param $aShadowColor
     * @param 102
     * @param 102
     * @param 102
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetShadow($aShowShadow=true,$aShadowWidth=5,$aShadowColor=array(102,102,102)) {
	$this->doshadow = $aShowShadow;
	$this->shadow_color = $aShadowColor;
	$this->shadow_width = $aShadowWidth;
	$this->footer->iBottomMargin += $aShadowWidth;
	$this->footer->iRightMargin += $aShadowWidth;
    }

    // Specify x,y scale. Note that if you manually specify the scale
    // you must also specify the tick distance with a call to Ticks::Set()
    /**
     * SetScale
     * Insert description here
     *
     * @param $aAxisType
     * @param $aYMin
     * @param $aYMax
     * @param $aXMin
     * @param $aXMax
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetScale($aAxisType,$aYMin=1,$aYMax=1,$aXMin=1,$aXMax=1) {
	$this->axtype = $aAxisType;

	if( $aYMax < $aYMin || $aXMax < $aXMin )
	    JpGraphError::RaiseL(25020);//('Graph::SetScale(): Specified Max value must be larger than the specified Min value.');

	$yt=substr($aAxisType,-3,3);
	if( $yt=="lin" )
	    $this->yscale = new LinearScale($aYMin,$aYMax);
	elseif( $yt == "int" ) {
	    $this->yscale = new LinearScale($aYMin,$aYMax);
	    $this->yscale->SetIntScale();
	}
	elseif( $yt=="log" )
	    $this->yscale = new LogScale($aYMin,$aYMax);
	else
	    JpGraphError::RaiseL(25021,$aAxisType);//("Unknown scale specification for Y-scale. ($aAxisType)");
			
	$xt=substr($aAxisType,0,3);
	if( $xt == "lin" || $xt == "tex" ) {
	    $this->xscale = new LinearScale($aXMin,$aXMax,"x");
	    $this->xscale->textscale = ($xt == "tex");
	}
	elseif( $xt == "int" ) {
	    $this->xscale = new LinearScale($aXMin,$aXMax,"x");
	    $this->xscale->SetIntScale();
	}
	elseif( $xt == "dat" ) {
	    $this->xscale = new DateScale($aXMin,$aXMax,"x");
	}
	elseif( $xt == "log" )
	    $this->xscale = new LogScale($aXMin,$aXMax,"x");
	else
	    JpGraphError::RaiseL(25022,$aAxisType);//(" Unknown scale specification for X-scale. ($aAxisType)");

	$this->xaxis = new Axis($this->img,$this->xscale);
	$this->yaxis = new Axis($this->img,$this->yscale);
	$this->xgrid = new Grid($this->xaxis);
	$this->ygrid = new Grid($this->yaxis);	
	$this->ygrid->Show();			
    }
	
    // Specify secondary Y scale
    /**
     * SetY2Scale
     * Insert description here
     *
     * @param $aAxisType
     * @param $aY2Min
     * @param $aY2Max
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetY2Scale($aAxisType="lin",$aY2Min=1,$aY2Max=1) {
	if( $aAxisType=="lin" ) 
	    $this->y2scale = new LinearScale($aY2Min,$aY2Max);
	elseif( $aAxisType == "int" ) {
	    $this->y2scale = new LinearScale($aY2Min,$aY2Max);
	    $this->y2scale->SetIntScale();
	}
	elseif( $aAxisType=="log" ) {
	    $this->y2scale = new LogScale($aY2Min,$aY2Max);
	}
	else JpGraphError::RaiseL(25023,$aAxisType);//("JpGraph: Unsupported Y2 axis type: $aAxisType\nMust be one of (lin,log,int)");
			
	$this->y2axis = new Axis($this->img,$this->y2scale);
	$this->y2axis->scale->ticks->SetDirection(SIDE_LEFT); 
	$this->y2axis->SetLabelSide(SIDE_RIGHT); 
	$this->y2axis->SetPos('max');
	$this->y2axis->SetTitleSide(SIDE_RIGHT);
		
	// Deafult position is the max x-value
	$this->y2grid = new Grid($this->y2axis);							
    }

    // Set the delta position (in pixels) between the multiple Y-axis
    /**
     * SetYDeltaDist
     * Insert description here
     *
     * @param $aDist
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetYDeltaDist($aDist) {
	$this->iYAxisDeltaPos = $aDist;
    }

    // Specify secondary Y scale
    /**
     * SetYScale
     * Insert description here
     *
     * @param $aN
     * @param $aAxisType
     * @param $aYMin
     * @param $aYMax
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetYScale($aN,$aAxisType="lin",$aYMin=1,$aYMax=1) {

	if( $aAxisType=="lin" ) 
	    $this->ynscale[$aN] = new LinearScale($aYMin,$aYMax);
	elseif( $aAxisType == "int" ) {
	    $this->ynscale[$aN] = new LinearScale($aYMin,$aYMax);
	    $this->ynscale[$aN]->SetIntScale();
	}
	elseif( $aAxisType=="log" ) {
	    $this->ynscale[$aN] = new LogScale($aYMin,$aYMax);
	}
	else JpGraphError::RaiseL(25024,$aAxisType);//("JpGraph: Unsupported Y axis type: $aAxisType\nMust be one of (lin,log,int)");
			
	$this->ynaxis[$aN] = new Axis($this->img,$this->ynscale[$aN]);
	$this->ynaxis[$aN]->scale->ticks->SetDirection(SIDE_LEFT); 
	$this->ynaxis[$aN]->SetLabelSide(SIDE_RIGHT); 
    }

    // Specify density of ticks when autoscaling 'normal', 'dense', 'sparse', 'verysparse'
    // The dividing factor have been determined heuristically according to my aesthetic 
    // sense (or lack off) y.m.m.v !
    /**
     * SetTickDensity
     * Insert description here
     *
     * @param $aYDensity
     * @param $aXDensity
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetTickDensity($aYDensity=TICKD_NORMAL,$aXDensity=TICKD_NORMAL) {
	$this->xtick_factor=30;
	$this->ytick_factor=25;		
	switch( $aYDensity ) {
	    case TICKD_DENSE:
		$this->ytick_factor=12;			
		break;
	    case TICKD_NORMAL:
		$this->ytick_factor=25;			
		break;
	    case TICKD_SPARSE:
		$this->ytick_factor=40;			
		break;
	    case TICKD_VERYSPARSE:
		$this->ytick_factor=100;			
		break;		
	    default:
		JpGraphError::RaiseL(25025,$densy);//("JpGraph: Unsupported Tick density: $densy");
	}
	switch( $aXDensity ) {
	    case TICKD_DENSE:
		$this->xtick_factor=15;							
		break;
	    case TICKD_NORMAL:
		$this->xtick_factor=30;			
		break;
	    case TICKD_SPARSE:
		$this->xtick_factor=45;					
		break;
	    case TICKD_VERYSPARSE:
		$this->xtick_factor=60;								
		break;		
	    default:
		JpGraphError::RaiseL(25025,$densx);//("JpGraph: Unsupported Tick density: $densx");
	}		
    }
	

    // Get a string of all image map areas	
    /**
     * GetCSIMareas
     * Insert description here
     *
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function GetCSIMareas() {
	if( !$this->iHasStroked )
	    $this->Stroke(_CSIM_SPECIALFILE);

	$csim = $this->title->GetCSIMAreas();
	$csim .= $this->subtitle->GetCSIMAreas();
	$csim .= $this->subsubtitle->GetCSIMAreas();
	$csim .= $this->legend->GetCSIMAreas();

	if( $this->y2axis != NULL ) {
	    $csim .= $this->y2axis->title->GetCSIMAreas();
	}

	if( $this->texts != null ) {
	    $n = count($this->texts);
	    for($i=0; $i < $n; ++$i ) {
		$csim .= $this->texts[$i]->GetCSIMAreas();
	    }
	}

	if( $this->y2texts != null && $this->y2scale != null ) {
	    $n = count($this->y2texts);
	    for($i=0; $i < $n; ++$i ) {
		$csim .= $this->y2texts[$i]->GetCSIMAreas();
	    }
	}

	if( $this->yaxis != null && $this->xaxis != null ) {
	    $csim .= $this->yaxis->title->GetCSIMAreas();	
	    $csim .= $this->xaxis->title->GetCSIMAreas();
	}

	$n = count($this->plots);
	for( $i=0; $i < $n; ++$i ) 
	    $csim .= $this->plots[$i]->GetCSIMareas();

	$n = count($this->y2plots);
	for( $i=0; $i < $n; ++$i ) 
	    $csim .= $this->y2plots[$i]->GetCSIMareas();

	$n = count($this->ynaxis);
	for( $i=0; $i < $n; ++$i ) {
	    $m = count($this->ynplots[$i]); 
	    for($j=0; $j < $m; ++$j ) {
		$csim .= $this->ynplots[$i][$j]->GetCSIMareas();
	    }
	}

	$n = count($this->iTables);
	for( $i=0; $i < $n; ++$i ) {
	    $csim .= $this->iTables[$i]->GetCSIMareas();
	}

	return $csim;
    }
	
    // Get a complete <MAP>..</MAP> tag for the final image map
    /**
     * GetHTMLImageMap
     * Insert description here
     *
     * @param $aMapName
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function GetHTMLImageMap($aMapName) {
	$im = "<map name=\"$aMapName\" id=\"$aMapName\" >\n";
	$im .= $this->GetCSIMareas();
	$im .= "</map>"; 
	return $im;
    }

    /**
     * CheckCSIMCache
     * Insert description here
     *
     * @param $aCacheName
     * @param $aTimeOut
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function CheckCSIMCache($aCacheName,$aTimeOut=60) {
	global $_SERVER;

	if( $aCacheName=='auto' )
	    $aCacheName=basename($_SERVER['PHP_SELF']);

	$urlarg = $this->GetURLArguments();
	$this->csimcachename = CSIMCACHE_DIR.$aCacheName.$urlarg;
	$this->csimcachetimeout = $aTimeOut;

	// First determine if we need to check for a cached version
	// This differs from the standard cache in the sense that the
	// image and CSIM map HTML file is written relative to the directory
	// the script executes in and not the specified cache directory.
	// The reason for this is that the cache directory is not necessarily
	// accessible from the HTTP server.
	if( $this->csimcachename != '' ) {
	    $dir = dirname($this->csimcachename);
	    $base = basename($this->csimcachename);
	    $base = strtok($base,'.');
	    $suffix = strtok('.');
	    $basecsim = $dir.'/'.$base.'?'.$urlarg.'_csim_.html';
	    $baseimg = $dir.'/'.$base.'?'.$urlarg.'.'.$this->img->img_format;

	    $timedout=false;
	    // Does it exist at all ?
	    
	    if( file_exists($basecsim) && file_exists($baseimg) ) {
		// Check that it hasn't timed out
		$diff=time()-filemtime($basecsim);
		if( $this->csimcachetimeout>0 && ($diff > $this->csimcachetimeout*60) ) {
		    $timedout=true;
		    @unlink($basecsim);
		    @unlink($baseimg);
		}
		else {
		    if ($fh = @fopen($basecsim, "r")) {
			fpassthru($fh);
			return true;
		    }
		    else
			JpGraphError::RaiseL(25027,$basecsim);//(" Can't open cached CSIM \"$basecsim\" for reading.");
		}
	    }
	}
	return false;
    }

    // Build the argument string to be used with the csim images
    /**
     * GetURLArguments
     * Insert description here
     *
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function GetURLArguments() {
		
	// This is a JPGRAPH internal defined that prevents
	// us from recursively coming here again
	$urlarg = _CSIM_DISPLAY.'=1';

	// Now reconstruct any user URL argument
	reset($_GET);
	while( list($key,$value) = each($_GET) ) {
	    if( is_array($value) ) {
		foreach ( $value as $k => $v ) {
		    $urlarg .= '&amp;'.$key.'%5B'.$k.'%5D='.urlencode($v);
		}
	    }
	    else {
		$urlarg .= '&amp;'.$key.'='.urlencode($value);
	    }
	}

	// It's not ideal to convert POST argument to GET arguments
	// but there is little else we can do. One idea for the 
	// future might be recreate the POST header in case.
	reset($_POST);
	while( list($key,$value) = each($_POST) ) {
	    if( is_array($value) ) {
		foreach ( $value as $k => $v ) {
		    $urlarg .= '&amp;'.$key.'%5B'.$k.'%5D='.urlencode($v);
		}
	    }
	    else {
		$urlarg .= '&amp;'.$key.'='.urlencode($value);
	    }
	}

	return $urlarg;
    }

    /**
     * SetCSIMImgAlt
     * Insert description here
     *
     * @param $aAlt
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetCSIMImgAlt($aAlt) {
	$this->iCSIMImgAlt = $aAlt;
    }

    /**
     * StrokeCSIM
     * Insert description here
     *
     * @param $aScriptName
     * @param $aCSIMName
     * @param $aBorder
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function StrokeCSIM($aScriptName='auto',$aCSIMName='',$aBorder=0) {
	if( $aCSIMName=='' ) {
	    // create a random map name
	    srand ((double) microtime() * 1000000);
	    $r = rand(0,100000);
	    $aCSIMName='__mapname'.$r.'__';
	}

	if( $aScriptName=='auto' )
	    $aScriptName=basename($_SERVER['PHP_SELF']);

	$urlarg = $this->GetURLArguments();

	if( empty($_GET[_CSIM_DISPLAY]) ) {
	    // First determine if we need to check for a cached version
	    // This differs from the standard cache in the sense that the
	    // image and CSIM map HTML file is written relative to the directory
	    // the script executes in and not the specified cache directory.
	    // The reason for this is that the cache directory is not necessarily
	    // accessible from the HTTP server.
	    if( $this->csimcachename != '' ) {
		$dir = dirname($this->csimcachename);
		$base = basename($this->csimcachename);
		$base = strtok($base,'.');
		$suffix = strtok('.');
		$basecsim = $dir.'/'.$base.'?'.$urlarg.'_csim_.html';
		$baseimg = $base.'?'.$urlarg.'.'.$this->img->img_format;

		// Check that apache can write to directory specified

		if( file_exists($dir) && !is_writeable($dir) ) {
		    JpgraphError::RaiseL(25028,$dir);//('Apache/PHP does not have permission to write to the CSIM cache directory ('.$dir.'). Check permissions.');
		}
		
		// Make sure directory exists
		$this->cache->MakeDirs($dir);

		// Write the image file
		$this->Stroke(CSIMCACHE_DIR.$baseimg);

		// Construct wrapper HTML and write to file and send it back to browser

		// In the src URL we must replace the '?' with its encoding to prevent the arguments
		// to be converted to real arguments.
		$tmp = str_replace('?','%3f',$baseimg);
		$htmlwrap = $this->GetHTMLImageMap($aCSIMName)."\n".
		    '<img src="'.CSIMCACHE_HTTP_DIR.$tmp.'" ismap="ismap" usemap="#'.$aCSIMName.'" border="'.$aBorder.'" width="'.$this->img->width.'" height="'.$this->img->height."\" alt=\"".$this->iCSIMImgAlt."\" />\n";

		if($fh =  @fopen($basecsim,'w') ) {
		    fwrite($fh,$htmlwrap);
		    fclose($fh);
		    echo $htmlwrap;
		}
		else
		    JpGraphError::RaiseL(25029,$basecsim);//(" Can't write CSIM \"$basecsim\" for writing. Check free space and permissions.");
	    }
	    else {

		if( $aScriptName=='' ) {
		    JpGraphError::RaiseL(25030);//('Missing script name in call to StrokeCSIM(). You must specify the name of the actual image script as the first parameter to StrokeCSIM().');
		}
		echo $this->GetHTMLImageMap($aCSIMName);
		echo "<img src=\"".$aScriptName.'?'.$urlarg."\" ismap=\"ismap\" usemap=\"#".$aCSIMName.'" border="'.$aBorder.'" width="'.$this->img->width.'" height="'.$this->img->height."\" alt=\"".$this->iCSIMImgAlt."\" />\n";
	    }
	}
	else {
	    $this->Stroke();
	}
    }

    /**
     * GetTextsYMinMax
     * Insert description here
     *
     * @param $aY2
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function GetTextsYMinMax($aY2=false) {
	if( $aY2 ) 
	    $txts = $this->y2texts;
	else
	    $txts = $this->texts;
	$n = count($txts);
	$min=null;
	$max=null;
	for( $i=0; $i < $n; ++$i ) {
	    if( $txts[$i]->iScalePosY !== null && 
		$txts[$i]->iScalePosX !== null  ) {
		if( $min === null  ) {
		    $min = $max = $txts[$i]->iScalePosY ;
		}
		else {
		    $min = min($min,$txts[$i]->iScalePosY);
		    $max = max($max,$txts[$i]->iScalePosY);
		}
	    }
	}
	if( $min !== null ) {
	    return array($min,$max);
	}
	else
	    return null;
    }

    /**
     * GetTextsXMinMax
     * Insert description here
     *
     * @param $aY2
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function GetTextsXMinMax($aY2=false) {
	if( $aY2 ) 
	    $txts = $this->y2texts;
	else
	    $txts = $this->texts;
	$n = count($txts);
	$min=null;
	$max=null;
	for( $i=0; $i < $n; ++$i ) {
	    if( $txts[$i]->iScalePosY !== null && 
		$txts[$i]->iScalePosX !== null  ) {
		if( $min === null  ) {
		    $min = $max = $txts[$i]->iScalePosX ;
		}
		else {
		    $min = min($min,$txts[$i]->iScalePosX);
		    $max = max($max,$txts[$i]->iScalePosX);
		}
	    }
	}
	if( $min !== null ) {
	    return array($min,$max);
	}
	else
	    return null;
    }

    /**
     * GetXMinMax
     * Insert description here
     *
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function GetXMinMax() {
	list($min,$ymin) = $this->plots[0]->Min();
	list($max,$ymax) = $this->plots[0]->Max();
	foreach( $this->plots as $p ) {
	    list($xmin,$ymin) = $p->Min();
	    list($xmax,$ymax) = $p->Max();			
	    $min = Min($xmin,$min);
	    $max = Max($xmax,$max);
	}

	if( $this->y2axis != null ) {
	    foreach( $this->y2plots as $p ) {
		list($xmin,$ymin) = $p->Min();
			list($xmax,$ymax) = $p->Max();			
			$min = Min($xmin,$min);
			$max = Max($xmax,$max);
	    }		    
	}

	$n = count($this->ynaxis);
	for( $i=0; $i < $n; ++$i ) {
	    if( $this->ynaxis[$i] != null) {
		foreach( $this->ynplots[$i] as $p ) {
		    list($xmin,$ymin) = $p->Min();
		    list($xmax,$ymax) = $p->Max();			
		    $min = Min($xmin,$min);
		    $max = Max($xmax,$max);
		}		    
	    }
	}
	return array($min,$max);
    }

    /**
     * AdjustMarginsForTitles
     * Insert description here
     *
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function AdjustMarginsForTitles() {
	$totrequired = 
	    ($this->title->t != '' ? 
	     $this->title->GetTextHeight($this->img) + $this->title->margin + 5 : 0 ) +
	    ($this->subtitle->t != '' ? 
	     $this->subtitle->GetTextHeight($this->img) + $this->subtitle->margin + 5 : 0 ) + 
	    ($this->subsubtitle->t != '' ? 
	     $this->subsubtitle->GetTextHeight($this->img) + $this->subsubtitle->margin + 5 : 0 ) ;
	

	$btotrequired = 0;
	if($this->xaxis != null &&  !$this->xaxis->hide && !$this->xaxis->hide_labels ) {
	    // Minimum bottom margin
	    if( $this->xaxis->title->t != '' ) {
		if( $this->img->a == 90 ) 
		    $btotrequired = $this->yaxis->title->GetTextHeight($this->img) + 5 ;
		else
		    $btotrequired = $this->xaxis->title->GetTextHeight($this->img) + 5 ;
	    }
	    else
		$btotrequired = 0;
	    
	    if( $this->img->a == 90 ) {
		$this->img->SetFont($this->yaxis->font_family,$this->yaxis->font_style,
				    $this->yaxis->font_size);
		$lh = $this->img->GetTextHeight('Mg',$this->yaxis->label_angle);
	    }
	    else {
		$this->img->SetFont($this->xaxis->font_family,$this->xaxis->font_style,
				    $this->xaxis->font_size);
		$lh = $this->img->GetTextHeight('Mg',$this->xaxis->label_angle);
	    }
	    
	    $btotrequired += $lh + 5;
	}

	if( $this->img->a == 90 ) {
	    // DO Nothing. It gets too messy to do this properly for 90 deg...
	}
	else{
	    if( $this->img->top_margin < $totrequired ) {
		$this->SetMargin($this->img->left_margin,$this->img->right_margin,
				 $totrequired,$this->img->bottom_margin);
	    }
	    if( $this->img->bottom_margin < $btotrequired ) {
		$this->SetMargin($this->img->left_margin,$this->img->right_margin,
				 $this->img->top_margin,$btotrequired);
	    }
	}
    }

    // Stroke the graph
    // $aStrokeFileName	If != "" the image will be written to this file and NOT
    // streamed back to the browser
    /**
     * Stroke
     * Insert description here
     *
     * @param $aStrokeFileName
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function Stroke($aStrokeFileName="") {		

	// Fist make a sanity check that user has specified a scale
	if( empty($this->yscale) ) {
	    JpGraphError::RaiseL(25031);//('You must specify what scale to use with a call to Graph::SetScale().');
	}

	// Start by adjusting the margin so that potential titles will fit.
	$this->AdjustMarginsForTitles();

	// Setup scale constants
	if( $this->yscale ) $this->yscale->InitConstants($this->img);
	if( $this->xscale ) $this->xscale->InitConstants($this->img);
	if( $this->y2scale ) $this->y2scale->InitConstants($this->img);
	
	$n=count($this->ynscale);
	for($i=0; $i < $n; ++$i) {
	  if( $this->ynscale[$i] ) $this->ynscale[$i]->InitConstants($this->img);
	}

	// If the filename is the predefined value = '_csim_special_'
	// we assume that the call to stroke only needs to do enough
	// to correctly generate the CSIM maps.
	// We use this variable to skip things we don't strictly need
	// to do to generate the image map to improve performance
	// a best we can. Therefor you will see a lot of tests !$_csim in the
	// code below.
	$_csim = ($aStrokeFileName===_CSIM_SPECIALFILE);

	// We need to know if we have stroked the plot in the
	// GetCSIMareas. Otherwise the CSIM hasn't been generated
	// and in the case of GetCSIM called before stroke to generate
	// CSIM without storing an image to disk GetCSIM must call Stroke.
	$this->iHasStroked = true;

	// Do any pre-stroke adjustment that is needed by the different plot types
	// (i.e bar plots want's to add an offset to the x-labels etc)
	for($i=0; $i < count($this->plots) ; ++$i ) {
	    $this->plots[$i]->PreStrokeAdjust($this);
	    $this->plots[$i]->DoLegend($this);
	}
		
	// Any plots on the second Y scale?
	if( $this->y2scale != null ) {
	    for($i=0; $i<count($this->y2plots)	; ++$i ) {
		$this->y2plots[$i]->PreStrokeAdjust($this);
		$this->y2plots[$i]->DoLegend($this);
	    }
	}

	// Any plots on the extra Y axes?
	$n = count($this->ynaxis);
	for($i=0; $i<$n	; ++$i ) {
	    if( $this->ynplots == null || $this->ynplots[$i] == null) {
		JpGraphError::RaiseL(25032,$i);//("No plots for Y-axis nbr:$i");
	    } 
	    $m = count($this->ynplots[$i]); 
	    for($j=0; $j < $m; ++$j ) {
		$this->ynplots[$i][$j]->PreStrokeAdjust($this);
		$this->ynplots[$i][$j]->DoLegend($this);
	    }
	}

	// Bail out if any of the Y-axis not been specified and
	// has no plots. (This means it is impossible to do autoscaling and
	// no other scale was given so we can't possible draw anything). If you use manual
	// scaling you also have to supply the tick steps as well.
	if( (!$this->yscale->IsSpecified() && count($this->plots)==0) ||
	    ($this->y2scale!=null && !$this->y2scale->IsSpecified() && count($this->y2plots)==0) ) {
	    //$e = "n=".count($this->y2plots)."\n";
	    // $e = "Can't draw unspecified Y-scale.<br>\nYou have either:<br>\n";
	    // $e .= "1. Specified an Y axis for autoscaling but have not supplied any plots<br>\n";
	    // $e .= "2. Specified a scale manually but have forgot to specify the tick steps";
	    JpGraphError::RaiseL(25026);
	}
		
	// Bail out if no plots and no specified X-scale
	if( (!$this->xscale->IsSpecified() && count($this->plots)==0 && count($this->y2plots)==0) )
	    JpGraphError::RaiseL(25034);//("<strong>JpGraph: Can't draw unspecified X-scale.</strong><br>No plots.<br>");

	//Check if we should autoscale y-axis
	if( !$this->yscale->IsSpecified() && count($this->plots)>0 ) {
	    list($min,$max) = $this->GetPlotsYMinMax($this->plots);
 	    $lres = $this->GetLinesYMinMax($this->lines);
	    if( is_array($lres) ) {
		list($linmin,$linmax) = $lres ;
		$min = min($min,$linmin);
		$max = max($max,$linmax);
	    }
	    $tres = $this->GetTextsYMinMax();
	    if( is_array($tres) ) {
		list($tmin,$tmax) = $tres ;
		$min = min($min,$tmin);
		$max = max($max,$tmax);
	    }
	    $this->yscale->AutoScale($this->img,$min,$max,
				     $this->img->plotheight/$this->ytick_factor);
	}
	elseif( $this->yscale->IsSpecified() && 
		( $this->yscale->auto_ticks || !$this->yscale->ticks->IsSpecified()) ) {
	    // The tick calculation will use the user suplied min/max values to determine
	    // the ticks. If auto_ticks is false the exact user specifed min and max
	    // values will be used for the scale. 
	    // If auto_ticks is true then the scale might be slightly adjusted
	    // so that the min and max values falls on an even major step.
	    $min = $this->yscale->scale[0];
	    $max = $this->yscale->scale[1];
	    $this->yscale->AutoScale($this->img,$min,$max,
				     $this->img->plotheight/$this->ytick_factor,
				     $this->yscale->auto_ticks);

	    // Now make sure we show enough precision to accurate display the
	    // labels. If this is not done then the user might end up with
	    // a scale that might actually start with, say 13.5, butdue to rounding
	    // the scale label will ony show 14.
	    if( abs(floor($min)-$min) > 0 ) {
		
		// If the user has set a format then we bail out
		if( $this->yscale->ticks->label_formatstr == '' && $this->yscale->ticks->label_dateformatstr == '' ) {
		    $this->yscale->ticks->precision = abs( floor(log10( abs(floor($min)-$min))) )+1;
		}
	    }
	}

	if( $this->y2scale != null) {
	    if( !$this->y2scale->IsSpecified() && count($this->y2plots)>0 ) {
		list($min,$max) = $this->GetPlotsYMinMax($this->y2plots);

		$lres = $this->GetLinesYMinMax($this->y2lines);
		if( is_array($lres) ) {
		    list($linmin,$linmax) = $lres ;
		    $min = min($min,$linmin);
		    $max = max($max,$linmax);
		}
		$tres = $this->GetTextsYMinMax(true);
		if( is_array($tres) ) {
		    list($tmin,$tmax) = $tres ;
		    $min = min($min,$tmin);
		    $max = max($max,$tmax);
		}
		$this->y2scale->AutoScale($this->img,$min,$max,$this->img->plotheight/$this->ytick_factor);
	    }			
	    elseif( $this->y2scale->IsSpecified() && 
		    ( $this->y2scale->auto_ticks || !$this->y2scale->ticks->IsSpecified()) ) {
		// The tick calculation will use the user suplied min/max values to determine
		// the ticks. If auto_ticks is false the exact user specifed min and max
		// values will be used for the scale. 
		// If auto_ticks is true then the scale might be slightly adjusted
		// so that the min and max values falls on an even major step.
		$min = $this->y2scale->scale[0];
		$max = $this->y2scale->scale[1];
		$this->y2scale->AutoScale($this->img,$min,$max,
					  $this->img->plotheight/$this->ytick_factor,
					  $this->y2scale->auto_ticks);

	    // Now make sure we show enough precision to accurate display the
	    // labels. If this is not done then the user might end up with
	    // a scale that might actually start with, say 13.5, butdue to rounding
	    // the scale label will ony show 14.
	    if( abs(floor($min)-$min) > 0 ) {
		
		// If the user has set a format then we bail out
		if( $this->y2scale->ticks->label_formatstr == '' && $this->y2scale->ticks->label_dateformatstr == '' ) {
		    $this->y2scale->ticks->precision = abs( floor(log10( abs(floor($min)-$min))) )+1;
		}
	    }

	    }
	}
				
	//
	// Autoscale the extra Y-axes
	//
	$n = count($this->ynaxis);
	for( $i=0; $i < $n; ++$i ) {
	  if( $this->ynscale[$i] != null) {
	    if( !$this->ynscale[$i]->IsSpecified() && count($this->ynplots[$i])>0 ) {
	      list($min,$max) = $this->GetPlotsYMinMax($this->ynplots[$i]);
	      $this->ynscale[$i]->AutoScale($this->img,$min,$max,$this->img->plotheight/$this->ytick_factor);
	    }			
	    elseif( $this->ynscale[$i]->IsSpecified() && 
		    ( $this->ynscale[$i]->auto_ticks || !$this->ynscale[$i]->ticks->IsSpecified()) ) {
		// The tick calculation will use the user suplied min/max values to determine
		// the ticks. If auto_ticks is false the exact user specifed min and max
		// values will be used for the scale. 
		// If auto_ticks is true then the scale might be slightly adjusted
		// so that the min and max values falls on an even major step.
	      $min = $this->ynscale[$i]->scale[0];
	      $max = $this->ynscale[$i]->scale[1];
	      $this->ynscale[$i]->AutoScale($this->img,$min,$max,
					    $this->img->plotheight/$this->ytick_factor,
					    $this->ynscale[$i]->auto_ticks);

	      // Now make sure we show enough precision to accurate display the
	      // labels. If this is not done then the user might end up with
	      // a scale that might actually start with, say 13.5, butdue to rounding
	      // the scale label will ony show 14.
	      if( abs(floor($min)-$min) > 0 ) {
		
		  // If the user has set a format then we bail out
		  if( $this->ynscale[$i]->ticks->label_formatstr == '' && $this->ynscale[$i]->ticks->label_dateformatstr == '' ) {
		      $this->ynscale[$i]->ticks->precision = abs( floor(log10( abs(floor($min)-$min))) )+1;
		  }
	      }

	    }
	  }
	}
		
	//Check if we should autoscale x-axis
	if( !$this->xscale->IsSpecified() ) {
	    if( substr($this->axtype,0,4) == "text" ) {
		$max=0;
		$n = count($this->plots);
		for($i=0; $i < $n; ++$i ) {
		    $p = $this->plots[$i];
		    // We need some unfortunate sub class knowledge here in order
		    // to increase number of data points in case it is a line plot
		    // which has the barcenter set. If not it could mean that the
		    // last point of the data is outside the scale since the barcenter
		    // settings means that we will shift the entire plot half a tick step
		    // to the right in oder to align with the center of the bars.
		    if( class_exists('BarPlot',false) ) {
			$cl = strtolower(get_class($p));
			if( (class_exists('BarPlot',false) && ($p instanceof BarPlot)) || 
			    empty($p->barcenter) ) 
			    $max=max($max,$p->numpoints-1);
			else {
			    $max=max($max,$p->numpoints);
			}
		    }
		    else {
			if( empty($p->barcenter) ) {
			    $max=max($max,$p->numpoints-1);
			}
			else {
			    $max=max($max,$p->numpoints);
			}
		    }
		}
		$min=0;
		if( $this->y2axis != null ) {
		    foreach( $this->y2plots as $p ) {
			$max=max($max,$p->numpoints-1);
		    }		    
		}
		$n = count($this->ynaxis);
		for( $i=0; $i < $n; ++$i ) {
		    if( $this->ynaxis[$i] != null) {
			foreach( $this->ynplots[$i] as $p ) {
			    $max=max($max,$p->numpoints-1);
			}		    
		    }
		}
		
		$this->xscale->Update($this->img,$min,$max);
		$this->xscale->ticks->Set($this->xaxis->tick_step,1);
		$this->xscale->ticks->SupressMinorTickMarks();
	    }
	    else {
		list($min,$max) = $this->GetXMinMax();

		$lres = $this->GetLinesXMinMax($this->lines);
		if( $lres ) {
		    list($linmin,$linmax) = $lres ;
		    $min = min($min,$linmin);
		    $max = max($max,$linmax);
		}

		$lres = $this->GetLinesXMinMax($this->y2lines);
		if( $lres ) {
		    list($linmin,$linmax) = $lres ;
		    $min = min($min,$linmin);
		    $max = max($max,$linmax);
		}

		$tres = $this->GetTextsXMinMax();
		if( $tres ) {
		    list($tmin,$tmax) = $tres ;
		    $min = min($min,$tmin);
		    $max = max($max,$tmax);
		}

		$tres = $this->GetTextsXMinMax(true);
		if( $tres ) {
		    list($tmin,$tmax) = $tres ;
		    $min = min($min,$tmin);
		    $max = max($max,$tmax);
		}

		$this->xscale->AutoScale($this->img,$min,$max,round($this->img->plotwidth/$this->xtick_factor));
	    }
			
	    //Adjust position of y-axis and y2-axis to minimum/maximum of x-scale
	    if( !is_numeric($this->yaxis->pos) && !is_string($this->yaxis->pos) )
	    	$this->yaxis->SetPos($this->xscale->GetMinVal());
	    if( $this->y2axis != null ) {
		if( !is_numeric($this->y2axis->pos) && !is_string($this->y2axis->pos) )
		    $this->y2axis->SetPos($this->xscale->GetMaxVal());
		$this->y2axis->SetTitleSide(SIDE_RIGHT);
	    }
	    $n = count($this->ynaxis);
	    $nY2adj = $this->y2axis != null ? $this->iYAxisDeltaPos : 0;
	    for( $i=0; $i < $n; ++$i ) { 
		if( $this->ynaxis[$i] != null ) {
		    if( !is_numeric($this->ynaxis[$i]->pos) && !is_string($this->ynaxis[$i]->pos) ) {
			$this->ynaxis[$i]->SetPos($this->xscale->GetMaxVal());
		  $this->ynaxis[$i]->SetPosAbsDelta($i*$this->iYAxisDeltaPos + $nY2adj);
		    }
		    $this->ynaxis[$i]->SetTitleSide(SIDE_RIGHT);
		}
	    }

	}	
	elseif( $this->xscale->IsSpecified() &&  
		( $this->xscale->auto_ticks || !$this->xscale->ticks->IsSpecified()) ) {
	    // The tick calculation will use the user suplied min/max values to determine
	    // the ticks. If auto_ticks is false the exact user specifed min and max
	    // values will be used for the scale. 
	    // If auto_ticks is true then the scale might be slightly adjusted
	    // so that the min and max values falls on an even major step.
	    $min = $this->xscale->scale[0];
	    $max = $this->xscale->scale[1];
	    $this->xscale->AutoScale($this->img,$min,$max,
				     round($this->img->plotwidth/$this->xtick_factor),
				     false);

	    // Now make sure we show enough precision to accurate display the
	    // labels. If this is not done then the user might end up with
	    // a scale that might actually start with, say 13.5, butdue to rounding
	    // the scale label will ony show 14.
	    if( abs(floor($min)-$min) > 0 ) {
		
		// If the user has set a format then we bail out
		if( $this->xscale->ticks->label_formatstr == '' && $this->xscale->ticks->label_dateformatstr == '' ) {
		    $this->xscale->ticks->precision = abs( floor(log10( abs(floor($min)-$min))) )+1;
		}
	    }


	    if( $this->y2axis != null ) {
		if( !is_numeric($this->y2axis->pos) && !is_string($this->y2axis->pos) )
		    $this->y2axis->SetPos($this->xscale->GetMaxVal());
		$this->y2axis->SetTitleSide(SIDE_RIGHT);
	    }

	}
		
	// If we have a negative values and x-axis position is at 0
	// we need to supress the first and possible the last tick since
	// they will be drawn on top of the y-axis (and possible y2 axis)
	// The test below might seem strange the reasone being that if
	// the user hasn't specified a value for position this will not
	// be set until we do the stroke for the axis so as of now it
	// is undefined.
	// For X-text scale we ignore all this since the tick are usually
	// much further in and not close to the Y-axis. Hence the test 
	// for 'text'	

	if( ($this->yaxis->pos==$this->xscale->GetMinVal() || 
	     (is_string($this->yaxis->pos) && $this->yaxis->pos=='min')) &&  
	    !is_numeric($this->xaxis->pos) && $this->yscale->GetMinVal() < 0 && 
	    substr($this->axtype,0,4) != 'text' && $this->xaxis->pos!="min" ) {

	    //$this->yscale->ticks->SupressZeroLabel(false);
	    $this->xscale->ticks->SupressFirst();
	    if( $this->y2axis != null ) {
		$this->xscale->ticks->SupressLast();
	    }
	}
	elseif( !is_numeric($this->yaxis->pos) && $this->yaxis->pos=='max' ) {
	    $this->xscale->ticks->SupressLast();
	}
	

	if( !$_csim ) {
	    $this->StrokePlotArea();
	    if( $this->iIconDepth == DEPTH_BACK ) {
		$this->StrokeIcons();
	    }
	}
	$this->StrokeAxis(false);

	// Stroke bands
	if( $this->bands != null && !$_csim) 
	    for($i=0; $i < count($this->bands); ++$i) {
		// Stroke all bands that asks to be in the background
		if( $this->bands[$i]->depth == DEPTH_BACK )
		    $this->bands[$i]->Stroke($this->img,$this->xscale,$this->yscale);
	    }

	if( $this->y2bands != null && $this->y2scale != null && !$_csim )
	    for($i=0; $i < count($this->y2bands); ++$i) {
		// Stroke all bands that asks to be in the foreground
		if( $this->y2bands[$i]->depth == DEPTH_BACK )
		    $this->y2bands[$i]->Stroke($this->img,$this->xscale,$this->y2scale);
	    }


	if( $this->grid_depth == DEPTH_BACK && !$_csim) {
	    $this->ygrid->Stroke();
	    $this->xgrid->Stroke();
	}
				
	// Stroke Y2-axis
	if( $this->y2axis != null && !$_csim) {		
	    $this->y2axis->Stroke($this->xscale); 				
	    $this->y2grid->Stroke();
	}

	// Stroke yn-axis
	$n = count($this->ynaxis); 
	for( $i=0; $i < $n; ++$i ) {
	    $this->ynaxis[$i]->Stroke($this->xscale); 				
	}

	$oldoff=$this->xscale->off;
	if(substr($this->axtype,0,4)=="text") {
	    if( $this->text_scale_abscenteroff > -1 ) {
		// For a text scale the scale factor is the number of pixel per step. 
		// Hence we can use the scale factor as a substitute for number of pixels
		// per major scale step and use that in order to adjust the offset so that
		// an object of width "abscenteroff" becomes centered.
		$this->xscale->off += round($this->xscale->scale_factor/2)-round($this->text_scale_abscenteroff/2);
	    }
	    else {
		$this->xscale->off += 
		    ceil($this->xscale->scale_factor*$this->text_scale_off*$this->xscale->ticks->minor_step);
	    }
	}

	if( $this->iDoClipping ) {
	    $oldimage = $this->img->CloneCanvasH();
	}

	if( ! $this->y2orderback ) {
	    // Stroke all plots for Y1 axis
	    for($i=0; $i < count($this->plots); ++$i) {
		$this->plots[$i]->Stroke($this->img,$this->xscale,$this->yscale);
		$this->plots[$i]->StrokeMargin($this->img);
	    }						
	}

	// Stroke all plots for Y2 axis
	if( $this->y2scale != null )
	    for($i=0; $i< count($this->y2plots); ++$i ) {	
		$this->y2plots[$i]->Stroke($this->img,$this->xscale,$this->y2scale);
	    }		

	if( $this->y2orderback ) {
	    // Stroke all plots for Y1 axis
	    for($i=0; $i < count($this->plots); ++$i) {
		$this->plots[$i]->Stroke($this->img,$this->xscale,$this->yscale);
		$this->plots[$i]->StrokeMargin($this->img);
	    }						
	}

	$n = count($this->ynaxis); 
	for( $i=0; $i < $n; ++$i ) {
	    $m = count($this->ynplots[$i]);
	    for( $j=0; $j < $m; ++$j ) { 
		$this->ynplots[$i][$j]->Stroke($this->img,$this->xscale,$this->ynscale[$i]);
		$this->ynplots[$i][$j]->StrokeMargin($this->img);
	    }
	}

	if( $this->iIconDepth == DEPTH_FRONT) {
	    $this->StrokeIcons();
	}
	
	if( $this->iDoClipping ) {
	    // Clipping only supports graphs at 0 and 90 degrees
	    if( $this->img->a == 0 ) {
		$this->img->CopyCanvasH($oldimage,$this->img->img,
					$this->img->left_margin,$this->img->top_margin,
					$this->img->left_margin,$this->img->top_margin,
					$this->img->plotwidth+1,$this->img->plotheight);
	    }
	    elseif( $this->img->a == 90 ) {
		$adj = ($this->img->height - $this->img->width)/2;
		$this->img->CopyCanvasH($oldimage,$this->img->img,
					$this->img->bottom_margin-$adj,$this->img->left_margin+$adj,
					$this->img->bottom_margin-$adj,$this->img->left_margin+$adj,
					$this->img->plotheight+1,$this->img->plotwidth);
	    }
	    else {
		JpGraphError::RaiseL(25035,$this->img->a);//('You have enabled clipping. Cliping is only supported for graphs at 0 or 90 degrees rotation. Please adjust you current angle (='.$this->img->a.' degrees) or disable clipping.');
	    }
	    $this->img->Destroy();
	    $this->img->SetCanvasH($oldimage);
	}

	$this->xscale->off=$oldoff;
		
	if( $this->grid_depth == DEPTH_FRONT && !$_csim ) {
	    $this->ygrid->Stroke();
	    $this->xgrid->Stroke();
	}

	// Stroke bands
	if( $this->bands!= null )
	    for($i=0; $i < count($this->bands); ++$i) {
		// Stroke all bands that asks to be in the foreground
		if( $this->bands[$i]->depth == DEPTH_FRONT )
		    $this->bands[$i]->Stroke($this->img,$this->xscale,$this->yscale);
	    }

	if( $this->y2bands!= null && $this->y2scale != null )
	    for($i=0; $i < count($this->y2bands); ++$i) {
		// Stroke all bands that asks to be in the foreground
		if( $this->y2bands[$i]->depth == DEPTH_FRONT )
		    $this->y2bands[$i]->Stroke($this->img,$this->xscale,$this->y2scale);
	    }


	// Stroke any lines added
	if( $this->lines != null ) {
	    for($i=0; $i < count($this->lines); ++$i) {
		$this->lines[$i]->Stroke($this->img,$this->xscale,$this->yscale);
		$this->lines[$i]->DoLegend($this);
	    }
	}

	if( $this->y2lines != null && $this->y2scale != null ) {
	    for($i=0; $i < count($this->y2lines); ++$i) {
		$this->y2lines[$i]->Stroke($this->img,$this->xscale,$this->y2scale);
		$this->y2lines[$i]->DoLegend($this);
	    }
	}

	// Finally draw the axis again since some plots may have nagged
	// the axis in the edges.
	if( !$_csim ) {
	    $this->StrokeAxis();
	}

	if( $this->y2scale != null && !$_csim ) 
	    $this->y2axis->Stroke($this->xscale,false); 	
		
	if( !$_csim ) {
	    $this->StrokePlotBox();
	}
		
	// The titles and legends never gets rotated so make sure
	// that the angle is 0 before stroking them				
	$aa = $this->img->SetAngle(0);
	$this->StrokeTitles();
	$this->footer->Stroke($this->img);
	$this->legend->Stroke($this->img);		
	$this->img->SetAngle($aa);	
	$this->StrokeTexts();	
	$this->StrokeTables();

	if( !$_csim ) {

	    $this->img->SetAngle($aa);	
			
	    // Draw an outline around the image map	
	    if(_JPG_DEBUG) {
		$this->DisplayClientSideaImageMapAreas();		
	    }
	    
	    // Should we do any final image transformation
	    if( $this->iImgTrans ) {
		if( !class_exists('ImgTrans',false) ) {
		    require_once('jpgraph_imgtrans.php');
		    //JpGraphError::Raise('In order to use image transformation you must include the file jpgraph_imgtrans.php in your script.');
		}
	       
		$tform = new ImgTrans($this->img->img);
		$this->img->img = $tform->Skew3D($this->iImgTransHorizon,$this->iImgTransSkewDist,
						 $this->iImgTransDirection,$this->iImgTransHighQ,
						 $this->iImgTransMinSize,$this->iImgTransFillColor,
						 $this->iImgTransBorder);
	    }

	    // If the filename is given as the special "__handle"
	    // then the image handler is returned and the image is NOT
	    // streamed back
	    if( $aStrokeFileName == _IMG_HANDLER ) {
		return $this->img->img;
	    }
	    else {
		// Finally stream the generated picture					
		$this->cache->PutAndStream($this->img,$this->cache_name,$this->inline,$aStrokeFileName);		
	    }
	}
    }

    /**
     * SetAxisLabelBackground
     * Insert description here
     *
     * @param $aType
     * @param $aXFColor
     * @param $aXColor
     * @param $aYFColor
     * @param $aYColor
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetAxisLabelBackground($aType,$aXFColor='lightgray',$aXColor='black',$aYFColor='lightgray',$aYColor='black') {
	$this->iAxisLblBgType = $aType;
	$this->iXAxisLblBgFillColor = $aXFColor;
	$this->iXAxisLblBgColor = $aXColor;
	$this->iYAxisLblBgFillColor = $aYFColor;
	$this->iYAxisLblBgColor = $aYColor;
    }

//---------------
// PRIVATE METHODS	

    /**
     * StrokeAxisLabelBackground
     * Insert description here
     *
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function StrokeAxisLabelBackground() {
	// Types 
	// 0 = No background
	// 1 = Only X-labels, length of axis
	// 2 = Only Y-labels, length of axis
	// 3 = As 1 but extends to width of graph
	// 4 = As 2 but extends to height of graph
	// 5 = Combination of 3 & 4
	// 6 = Combination of 1 & 2
 
	$t = $this->iAxisLblBgType ;
	if( $t < 1 ) return;
	// Stroke optional X-axis label background color
	if( $t == 1 || $t == 3 || $t == 5 || $t == 6 ) {
	    $this->img->PushColor($this->iXAxisLblBgFillColor);
	    if( $t == 1 || $t == 6 ) {
		$xl = $this->img->left_margin;
		$yu = $this->img->height - $this->img->bottom_margin + 1;
		$xr = $this->img->width - $this->img->right_margin ;
		$yl = $this->img->height-1-$this->frame_weight;
	    }
	    else { // t==3 || t==5
		$xl = $this->frame_weight;
		$yu = $this->img->height - $this->img->bottom_margin + 1;
		$xr = $this->img->width - 1 - $this->frame_weight;
		$yl = $this->img->height-1-$this->frame_weight;
	    }

	    $this->img->FilledRectangle($xl,$yu,$xr,$yl);
	    $this->img->PopColor();

	    // Check if we should add the vertical lines at left and right edge
	    if( $this->iXAxisLblBgColor !== '' ) {
		$this->img->PushColor($this->iXAxisLblBgColor);
		if( $t == 1 || $t == 6 ) {
		    $this->img->Line($xl,$yu,$xl,$yl);
		    $this->img->Line($xr,$yu,$xr,$yl);
		}
		else {
		    $xl = $this->img->width - $this->img->right_margin ;
		    $this->img->Line($xl,$yu-1,$xr,$yu-1);
		}
		$this->img->PopColor();
	    }
	}

	if( $t == 2 || $t == 4 || $t == 5 || $t == 6 ) {
	    $this->img->PushColor($this->iYAxisLblBgFillColor);
	    if( $t == 2 || $t == 6 ) {	    
		$xl = $this->frame_weight;
		$yu = $this->frame_weight+$this->img->top_margin;
		$xr = $this->img->left_margin - 1;
		$yl = $this->img->height - $this->img->bottom_margin + 1;
	    }
	    else {
		$xl = $this->frame_weight;
		$yu = $this->frame_weight;
		$xr = $this->img->left_margin - 1;
		$yl = $this->img->height-1-$this->frame_weight;
	    }

	    $this->img->FilledRectangle($xl,$yu,$xr,$yl);
	    $this->img->PopColor();

	    // Check if we should add the vertical lines at left and right edge
	    if( $this->iXAxisLblBgColor !== '' ) {
		$this->img->PushColor($this->iXAxisLblBgColor);
		if( $t == 2 || $t == 6 ) {
		    $this->img->Line($xl,$yu-1,$xr,$yu-1);
		    $this->img->Line($xl,$yl-1,$xr,$yl-1);		    
		}
		else {
		    $this->img->Line($xr+1,$yu,$xr+1,$this->img->top_margin);		    
		}
		$this->img->PopColor();
	    }

	}
    }

    /**
     * StrokeAxis
     * Insert description here
     *
     * @param $aStrokeLabels
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function StrokeAxis($aStrokeLabels=true) {
	
	if( $aStrokeLabels ) {
	    $this->StrokeAxisLabelBackground();
	}

	// Stroke axis
	if( $this->iAxisStyle != AXSTYLE_SIMPLE ) {
	    switch( $this->iAxisStyle ) {
	        case AXSTYLE_BOXIN :
	            $toppos = SIDE_DOWN;
		    $bottompos = SIDE_UP;
	            $leftpos = SIDE_RIGHT;
	            $rightpos = SIDE_LEFT;
	            break;
		case AXSTYLE_BOXOUT :
		    $toppos = SIDE_UP;
	            $bottompos = SIDE_DOWN;	    
	            $leftpos = SIDE_LEFT;
		    $rightpos = SIDE_RIGHT;
	            break;
		case AXSTYLE_YBOXIN:
	            $toppos = FALSE; 
		    $bottompos = SIDE_UP;
	            $leftpos = SIDE_RIGHT;
	            $rightpos = SIDE_LEFT;
		    break;
		case AXSTYLE_YBOXOUT:
		    $toppos = FALSE;
	            $bottompos = SIDE_DOWN;	    
	            $leftpos = SIDE_LEFT;
		    $rightpos = SIDE_RIGHT;
		    break;
		default:
	            JpGRaphError::RaiseL(25036,$this->iAxisStyle); //('Unknown AxisStyle() : '.$this->iAxisStyle);
	            break;
	    }
	    
	    // By default we hide the first label so it doesn't cross the
	    // Y-axis in case the positon hasn't been set by the user.
	    // However, if we use a box we always want the first value
	    // displayed so we make sure it will be displayed.
	    $this->xscale->ticks->SupressFirst(false);    

	    // Now draw the bottom X-axis
	    $this->xaxis->SetPos('min');
	    $this->xaxis->SetLabelSide(SIDE_DOWN);
	    $this->xaxis->scale->ticks->SetSide($bottompos);
	    $this->xaxis->Stroke($this->yscale,$aStrokeLabels);

	    if( $toppos !== FALSE ) {
		// We also want a top X-axis
		$this->xaxis = $this->xaxis;
		$this->xaxis->SetPos('max');
		$this->xaxis->SetLabelSide(SIDE_UP);
		// No title for the top X-axis 
		if( $aStrokeLabels ) {
		    $this->xaxis->title->Set('');
		}
		$this->xaxis->scale->ticks->SetSide($toppos);
		$this->xaxis->Stroke($this->yscale,$aStrokeLabels);
	    }

	    // Stroke the left Y-axis
	    $this->yaxis->SetPos('min');
	    $this->yaxis->SetLabelSide(SIDE_LEFT);
	    $this->yaxis->scale->ticks->SetSide($leftpos);
	    $this->yaxis->Stroke($this->xscale,$aStrokeLabels);

	    // Stroke the  right Y-axis
	    $this->yaxis->SetPos('max');
	    // No title for the right side 
	    if( $aStrokeLabels ) {
		$this->yaxis->title->Set('');
	    }
	    $this->yaxis->SetLabelSide(SIDE_RIGHT);
	    $this->yaxis->scale->ticks->SetSide($rightpos);
	    $this->yaxis->Stroke($this->xscale,$aStrokeLabels);  
	}
	else {
	    $this->xaxis->Stroke($this->yscale,$aStrokeLabels);
	    $this->yaxis->Stroke($this->xscale,$aStrokeLabels);		
	}
    }


    // Private helper function for backgound image
    /**
     * LoadBkgImage
     * Insert description here
     *
     * @param $aImgFormat
     * @param $aFile
     * @param $aImgStr
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    static function LoadBkgImage($aImgFormat='',$aFile='',$aImgStr='') {
	if( $aImgStr != '' ) {
	    return Image::CreateFromString($aImgStr);
	}

	// Remove case sensitivity and setup appropriate function to create image
	// Get file extension. This should be the LAST '.' separated part of the filename
	$e = explode('.',$aFile);
	$ext = strtolower($e[count($e)-1]);
	if ($ext == "jpeg")  {
	    $ext = "jpg";
	}
	
	if( trim($ext) == '' ) 
	    $ext = 'png';  // Assume PNG if no extension specified

	if( $aImgFormat == '' )
	    $imgtag = $ext;
	else
	    $imgtag = $aImgFormat;

	$supported = imagetypes();
	if( ( $ext == 'jpg' && !($supported & IMG_JPG) ) ||
	    ( $ext == 'gif' && !($supported & IMG_GIF) ) ||
	    ( $ext == 'png' && !($supported & IMG_PNG) ) ||
	    ( $ext == 'bmp' && !($supported & IMG_WBMP) ) ||
	    ( $ext == 'xpm' && !($supported & IMG_XPM) ) ) {

	    JpGraphError::RaiseL(25037,$aFile);//('The image format of your background image ('.$aFile.') is not supported in your system configuration. ');
	}


	if( $imgtag == "jpg" || $imgtag == "jpeg")
	{
	    $f = "imagecreatefromjpeg";
	    $imgtag = "jpg";
	}
	else
	{
	    $f = "imagecreatefrom".$imgtag;
	}

	// Compare specified image type and file extension
	if( $imgtag != $ext ) {
	    //$t = "Background image seems to be of different type (has different file extension) than specified imagetype. Specified: '".$aImgFormat."'File: '".$aFile."'";
	    JpGraphError::RaiseL(25038, $aImgFormat, $aFile);
	}

	$img = @$f($aFile);
	if( !$img ) {
	    JpGraphError::RaiseL(25039,$aFile);//(" Can't read background image: '".$aFile."'");   
	}
	return $img;
    }	

    /**
     * StrokeBackgroundGrad
     * Insert description here
     *
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function StrokeBackgroundGrad() {
	if( $this->bkg_gradtype < 0  ) 
	    return;
	$grad = new Gradient($this->img);
	if( $this->bkg_gradstyle == BGRAD_PLOT ) {
	    $xl = $this->img->left_margin;
	    $yt = $this->img->top_margin;
	    $xr = $xl + $this->img->plotwidth+1 ;
	    $yb = $yt + $this->img->plotheight ; 
	    $grad->FilledRectangle($xl,$yt,$xr,$yb,$this->bkg_gradfrom,$this->bkg_gradto,$this->bkg_gradtype);
	}
	else {
	    $xl = 0;
	    $yt = 0;
	    $xr = $xl + $this->img->width - 1;
	    $yb = $yt + $this->img->height ;
	    if( $this->doshadow  ) {
		$xr -= $this->shadow_width; 
		$yb -= $this->shadow_width; 
	    }
	    if( $this->doframe ) {
		$yt += $this->frame_weight;
		$yb -= $this->frame_weight;    
		$xl += $this->frame_weight;
		$xr -= $this->frame_weight;
	    }
	    $aa = $this->img->SetAngle(0);
	    $grad->FilledRectangle($xl,$yt,$xr,$yb,$this->bkg_gradfrom,$this->bkg_gradto,$this->bkg_gradtype);
	    $aa = $this->img->SetAngle($aa);
	}
    }

    /**
     * StrokeFrameBackground
     * Insert description here
     *
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function StrokeFrameBackground() {
	if( $this->background_image != "" && $this->background_cflag != "" ) {
	    JpGraphError::RaiseL(25040);//('It is not possible to specify both a background image and a background country flag.');
	}
	if( $this->background_image != "" ) {
	    $bkgimg = $this->LoadBkgImage($this->background_image_format,$this->background_image);
	}
	elseif( $this->background_cflag != "" ) {
	    if( ! class_exists('FlagImages',false) ) {
		JpGraphError::RaiseL(25041);//('In order to use Country flags as backgrounds you must include the "jpgraph_flags.php" file.');
	    }
	    $fobj = new FlagImages(FLAGSIZE4);
	    $dummy='';
	    $bkgimg = $fobj->GetImgByName($this->background_cflag,$dummy);
	    $this->background_image_mix = $this->background_cflag_mix;
	    $this->background_image_type = $this->background_cflag_type;
	}
	else {
	    return ;
	}

	$bw = ImageSX($bkgimg);
	$bh = ImageSY($bkgimg);

	// No matter what the angle is we always stroke the image and frame
	// assuming it is 0 degree
	$aa = $this->img->SetAngle(0);
		
	switch( $this->background_image_type ) {
	    case BGIMG_FILLPLOT: // Resize to just fill the plotarea
		$this->FillMarginArea();
		$this->StrokeFrame();
		// Special case to hande 90 degree rotated graph corectly
		if( $aa == 90 ) {
		    $this->img->SetAngle(90);
		    $this->FillPlotArea();
		    $aa = $this->img->SetAngle(0);
		    $adj = ($this->img->height - $this->img->width)/2;
		    $this->img->CopyMerge($bkgimg,
					  $this->img->bottom_margin-$adj,$this->img->left_margin+$adj,
					  0,0,
					  $this->img->plotheight+1,$this->img->plotwidth,
					  $bw,$bh,$this->background_image_mix);

		}
		else {
		    $this->FillPlotArea();
		    $this->img->CopyMerge($bkgimg,
					  $this->img->left_margin,$this->img->top_margin,
					  0,0,$this->img->plotwidth+1,$this->img->plotheight,
					  $bw,$bh,$this->background_image_mix);
		}
		break;
	    case BGIMG_FILLFRAME: // Fill the whole area from upper left corner, resize to just fit
		$hadj=0; $vadj=0;
		if( $this->doshadow ) {
		    $hadj = $this->shadow_width;
		    $vadj = $this->shadow_width;
		}
		$this->FillMarginArea();
		$this->FillPlotArea();
		$this->img->CopyMerge($bkgimg,0,0,0,0,$this->img->width-$hadj,$this->img->height-$vadj,
				      $bw,$bh,$this->background_image_mix);
		$this->StrokeFrame();
		break;
	    case BGIMG_COPY: // Just copy the image from left corner, no resizing
		$this->FillMarginArea();
		$this->FillPlotArea();
		$this->img->CopyMerge($bkgimg,0,0,0,0,$bw,$bh,
				      $bw,$bh,$this->background_image_mix);
		$this->StrokeFrame();
		break;
	    case BGIMG_CENTER: // Center original image in the plot area
		$this->FillMarginArea();
		$this->FillPlotArea();
		$centerx = round($this->img->plotwidth/2+$this->img->left_margin-$bw/2);
		$centery = round($this->img->plotheight/2+$this->img->top_margin-$bh/2);
		$this->img->CopyMerge($bkgimg,$centerx,$centery,0,0,$bw,$bh,
				      $bw,$bh,$this->background_image_mix);
		$this->StrokeFrame();
		break;
	    default:
		JpGraphError::RaiseL(25042);//(" Unknown background image layout");
	}			
	$this->img->SetAngle($aa);		
    }

    // Private
    // Draw a frame around the image
    /**
     * StrokeFrame
     * Insert description here
     *
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function StrokeFrame() {
	if( !$this->doframe ) return;

	if( $this->background_image_type <= 1 && 
	    ($this->bkg_gradtype < 0 || ($this->bkg_gradtype > 0 && $this->bkg_gradstyle==BGRAD_PLOT)) ) { 
	    $c = $this->margin_color;
	}
	else {
	    $c = false;
	}
	
	if( $this->doshadow ) {
	    $this->img->SetColor($this->frame_color);			
	    $this->img->ShadowRectangle(0,0,$this->img->width,$this->img->height,
					$c,$this->shadow_width,$this->shadow_color);
	}
	elseif( $this->framebevel ) {
	    if( $c ) {
		$this->img->SetColor($this->margin_color);
		$this->img->FilledRectangle(0,0,$this->img->width-1,$this->img->height-1); 
	    }
	    $this->img->Bevel(1,1,$this->img->width-2,$this->img->height-2,
			      $this->framebeveldepth,
			      $this->framebevelcolor1,$this->framebevelcolor2);
	    if( $this->framebevelborder ) {
		$this->img->SetColor($this->framebevelbordercolor);
		$this->img->Rectangle(0,0,$this->img->width-1,$this->img->height-1);
	    }
	}
	else {
	    $this->img->SetLineWeight($this->frame_weight);
	    if( $c ) {
		$this->img->SetColor($this->margin_color);
		$this->img->FilledRectangle(0,0,$this->img->width-1,$this->img->height-1); 
	    }
	    $this->img->SetColor($this->frame_color);
	    $this->img->Rectangle(0,0,$this->img->width-1,$this->img->height-1);		
	}
    }

    /**
     * FillMarginArea
     * Insert description here
     *
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function FillMarginArea() {
	$hadj=0; $vadj=0;
	if( $this->doshadow ) {
	    $hadj = $this->shadow_width;
	    $vadj = $this->shadow_width;
	}

	$this->img->SetColor($this->margin_color);
//	$this->img->FilledRectangle(0,0,$this->img->width-1-$hadj,$this->img->height-1-$vadj); 

	$this->img->FilledRectangle(0,0,$this->img->width-1-$hadj,$this->img->top_margin); 
	$this->img->FilledRectangle(0,$this->img->top_margin,$this->img->left_margin,$this->img->height-1-$hadj); 
	$this->img->FilledRectangle($this->img->left_margin+1,
				    $this->img->height-$this->img->bottom_margin,
				    $this->img->width-1-$hadj,
				    $this->img->height-1-$hadj); 
	$this->img->FilledRectangle($this->img->width-$this->img->right_margin,
				    $this->img->top_margin+1,
				    $this->img->width-1-$hadj,
				    $this->img->height-$this->img->bottom_margin-1); 
    }

    /**
     * FillPlotArea
     * Insert description here
     *
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function FillPlotArea() {
	$this->img->PushColor($this->plotarea_color);
	$this->img->FilledRectangle($this->img->left_margin,
				    $this->img->top_margin,
				    $this->img->width-$this->img->right_margin,
				    $this->img->height-$this->img->bottom_margin);	
	$this->img->PopColor();
    }
    
    // Stroke the plot area with either a solid color or a background image
    /**
     * StrokePlotArea
     * Insert description here
     *
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function StrokePlotArea() {
	// Note: To be consistent we really should take a possible shadow
	// into account. However, that causes some problem for the LinearScale class
	// since in the current design it does not have any links to class Graph which
	// means it has no way of compensating for the adjusted plotarea in case of a 
	// shadow. So, until I redesign LinearScale we can't compensate for this.
	// So just set the two adjustment parameters to zero for now.
	$boxadj = 0; //$this->doframe ? $this->frame_weight : 0 ;
	$adj = 0; //$this->doshadow ? $this->shadow_width : 0 ;

	if( $this->background_image != "" || $this->background_cflag != "" ) {
	    $this->StrokeFrameBackground();
	}
	else {
	    $aa = $this->img->SetAngle(0);
	    $this->StrokeFrame();
	    $aa = $this->img->SetAngle($aa);
	    $this->StrokeBackgroundGrad();
	    if( $this->bkg_gradtype < 0 || 
		($this->bkg_gradtype > 0 && $this->bkg_gradstyle==BGRAD_MARGIN) ) {
		$this->FillPlotArea();
	    }
	}	
    }	

    /**
     * StrokeIcons
     * Insert description here
     *
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function StrokeIcons() {
	$n = count($this->iIcons);
	for( $i=0; $i < $n; ++$i ) {
	    $this->iIcons[$i]->StrokeWithScale($this->img,$this->xscale,$this->yscale);
	}
    }
	
    /**
     * StrokePlotBox
     * Insert description here
     *
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function StrokePlotBox() {
	// Should we draw a box around the plot area?
	if( $this->boxed ) {
	    $this->img->SetLineWeight(1);
	    $this->img->SetLineStyle('solid');
	    $this->img->SetColor($this->box_color);
	    for($i=0; $i < $this->box_weight; ++$i ) {
		$this->img->Rectangle(
		    $this->img->left_margin-$i,$this->img->top_margin-$i,
		    $this->img->width-$this->img->right_margin+$i,
		    $this->img->height-$this->img->bottom_margin+$i);
	    }
	}						
    }		

    /**
     * SetTitleBackgroundFillStyle
     * Insert description here
     *
     * @param $aStyle
     * @param $aColor1
     * @param $aColor2
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetTitleBackgroundFillStyle($aStyle,$aColor1='black',$aColor2='white') {
	$this->titlebkg_fillstyle = $aStyle;
	$this->titlebkg_scolor1 = $aColor1;
	$this->titlebkg_scolor2 = $aColor2;
    }

    /**
     * SetTitleBackground
     * Insert description here
     *
     * @param $aBackColor
     * @param $aStyle
     * @param $aFrameStyle
     * @param $aFrameColor
     * @param $aFrameWeight
     * @param $aBevelHeight
     * @param $aEnable
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetTitleBackground($aBackColor='gray', $aStyle=TITLEBKG_STYLE1, $aFrameStyle=TITLEBKG_FRAME_NONE, $aFrameColor='black', $aFrameWeight=1, $aBevelHeight=3, $aEnable=true) {
	$this->titlebackground = $aEnable;
	$this->titlebackground_color = $aBackColor;
	$this->titlebackground_style = $aStyle;
	$this->titlebackground_framecolor = $aFrameColor;
	$this->titlebackground_framestyle = $aFrameStyle;
	$this->titlebackground_frameweight = $aFrameWeight;	
	$this->titlebackground_bevelheight = $aBevelHeight ;
    }


    /**
     * StrokeTitles
     * Insert description here
     *
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function StrokeTitles() {

	$margin=3;

	if( $this->titlebackground ) {

	    // Find out height
	    $this->title->margin += 2 ;
	    $h = $this->title->GetTextHeight($this->img)+$this->title->margin+$margin;
	    if( $this->subtitle->t != "" && !$this->subtitle->hide ) {
		$h += $this->subtitle->GetTextHeight($this->img)+$margin+
		    $this->subtitle->margin;
		$h += 2;
	    }
	    if( $this->subsubtitle->t != "" && !$this->subsubtitle->hide ) {
		$h += $this->subsubtitle->GetTextHeight($this->img)+$margin+
		    $this->subsubtitle->margin;
		$h += 2;
	    }
	    $this->img->PushColor($this->titlebackground_color);
	    if( $this->titlebackground_style === TITLEBKG_STYLE1 ) {
		// Inside the frame
		if( $this->framebevel ) {
		    $x1 = $y1 = $this->framebeveldepth + 1 ;
		    $x2 = $this->img->width - $this->framebeveldepth - 2 ; 
		    $this->title->margin += $this->framebeveldepth + 1 ;
		    $h += $y1 ;
		    $h += 2;
		}
		else {
		    $x1 = $y1 = $this->frame_weight;
		    $x2 = $this->img->width - 2*$x1;
		}
	    }
	    elseif( $this->titlebackground_style === TITLEBKG_STYLE2 ) {
		// Cover the frame as well
		$x1 = $y1 = 0;
		$x2 = $this->img->width - 1 ;
	    }
	    elseif( $this->titlebackground_style === TITLEBKG_STYLE3 ) {
		// Cover the frame as well (the difference is that
		// for style==3 a bevel frame border is on top
		// of the title background)
		$x1 = $y1 = 0;
		$x2 = $this->img->width - 1 ;
		$h += $this->framebeveldepth ;
		$this->title->margin += $this->framebeveldepth ;
	    }
	    else {
		JpGraphError::RaiseL(25043);//('Unknown title background style.');
	    }

	    if( $this->titlebackground_framestyle === 3 ) {
		$h += $this->titlebackground_bevelheight*2 + 1  ;
		$this->title->margin += $this->titlebackground_bevelheight ;
	    }

	    if( $this->doshadow ) {
		$x2 -= $this->shadow_width ;
	    }
	    
	    $indent=0;
	    if( $this->titlebackground_framestyle == TITLEBKG_FRAME_BEVEL ) {
		$ind = $this->titlebackground_bevelheight;
	    }

	    if( $this->titlebkg_fillstyle==TITLEBKG_FILLSTYLE_HSTRIPED ) {
		$this->img->FilledRectangle2($x1+$ind,$y1+$ind,$x2-$ind,$h-$ind,
					     $this->titlebkg_scolor1,
					     $this->titlebkg_scolor2);
	    }
	    elseif( $this->titlebkg_fillstyle==TITLEBKG_FILLSTYLE_VSTRIPED ) {
		$this->img->FilledRectangle2($x1+$ind,$y1+$ind,$x2-$ind,$h-$ind,
					     $this->titlebkg_scolor1,
					     $this->titlebkg_scolor2,2);
	    }
	    else {
		// Solid fill
		$this->img->FilledRectangle($x1,$y1,$x2,$h);
	    }
	    $this->img->PopColor();

	    $this->img->PushColor($this->titlebackground_framecolor);
	    $this->img->SetLineWeight($this->titlebackground_frameweight);
	    if( $this->titlebackground_framestyle == TITLEBKG_FRAME_FULL ) {
		// Frame background
		$this->img->Rectangle($x1,$y1,$x2,$h);
	    }
	    elseif( $this->titlebackground_framestyle == TITLEBKG_FRAME_BOTTOM ) {
		// Bottom line only
		$this->img->Line($x1,$h,$x2,$h);
	    }
	    elseif( $this->titlebackground_framestyle == TITLEBKG_FRAME_BEVEL ) {
		$this->img->Bevel($x1,$y1,$x2,$h,$this->titlebackground_bevelheight);
	    }
	    $this->img->PopColor();

	    // This is clumsy. But we neeed to stroke the whole graph frame if it is
	    // set to bevel to get the bevel shading on top of the text background
	    if( $this->framebevel && $this->doframe && 
		$this->titlebackground_style === 3 ) {
		$this->img->Bevel(1,1,$this->img->width-2,$this->img->height-2,
				  $this->framebeveldepth,
				  $this->framebevelcolor1,$this->framebevelcolor2);
		if( $this->framebevelborder ) {
		    $this->img->SetColor($this->framebevelbordercolor);
		    $this->img->Rectangle(0,0,$this->img->width-1,$this->img->height-1);
		}
	    }
	}

	// Stroke title
	$y = $this->title->margin; 
	if( $this->title->halign == 'center' ) 
	    $this->title->Center(0,$this->img->width,$y);
	elseif( $this->title->halign == 'left' ) {
	    $this->title->SetPos($this->title->margin+2,$y);
	}
	elseif( $this->title->halign == 'right' ) {
	    $indent = 0;
	    if( $this->doshadow ) 
		$indent = $this->shadow_width+2;
	    $this->title->SetPos($this->img->width-$this->title->margin-$indent,$y,'right');	    
	}
	$this->title->Stroke($this->img);
		
	// ... and subtitle
	$y += $this->title->GetTextHeight($this->img) + $margin + $this->subtitle->margin;
	if( $this->subtitle->halign == 'center' ) 
	    $this->subtitle->Center(0,$this->img->width,$y);
	elseif( $this->subtitle->halign == 'left' ) {
	    $this->subtitle->SetPos($this->subtitle->margin+2,$y);
	}
	elseif( $this->subtitle->halign == 'right' ) {
	    $indent = 0;
	    if( $this->doshadow ) 
		$indent = $this->shadow_width+2;
	    $this->subtitle->SetPos($this->img->width-$this->subtitle->margin-$indent,$y,'right'); 
	}
	$this->subtitle->Stroke($this->img);

	// ... and subsubtitle
	$y += $this->subtitle->GetTextHeight($this->img) + $margin + $this->subsubtitle->margin;
	if( $this->subsubtitle->halign == 'center' ) 
	    $this->subsubtitle->Center(0,$this->img->width,$y);
	elseif( $this->subsubtitle->halign == 'left' ) {
	    $this->subsubtitle->SetPos($this->subsubtitle->margin+2,$y);
	}
	elseif( $this->subsubtitle->halign == 'right' ) {
	    $indent = 0;
	    if( $this->doshadow ) 
		$indent = $this->shadow_width+2;
	    $this->subsubtitle->SetPos($this->img->width-$this->subsubtitle->margin-$indent,$y,'right'); 
	}
	$this->subsubtitle->Stroke($this->img);

	// ... and fancy title
	$this->tabtitle->Stroke($this->img);

    }

    /**
     * StrokeTexts
     * Insert description here
     *
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function StrokeTexts() {
	// Stroke any user added text objects
	if( $this->texts != null ) {
	    for($i=0; $i < count($this->texts); ++$i) {
		$this->texts[$i]->StrokeWithScale($this->img,$this->xscale,$this->yscale);
	    }
	}

	if( $this->y2texts != null && $this->y2scale != null ) {
	    for($i=0; $i < count($this->y2texts); ++$i) {
		$this->y2texts[$i]->StrokeWithScale($this->img,$this->xscale,$this->y2scale);
	    }
	}

    }

    /**
     * StrokeTables
     * Insert description here
     *
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function StrokeTables() {
	if( $this->iTables != null ) {
	    $n = count($this->iTables);
	    for( $i=0; $i < $n; ++$i ) {
		$this->iTables[$i]->StrokeWithScale($this->img,$this->xscale,$this->yscale);
	    }
	}
    }

    /**
     * DisplayClientSideaImageMapAreas
     * Insert description here
     *
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function DisplayClientSideaImageMapAreas() {
	// Debug stuff - display the outline of the image map areas
	$csim='';
	foreach ($this->plots as $p) {
	    $csim.= $p->GetCSIMareas();
	}
	$csim .= $this->legend->GetCSIMareas();
	if (preg_match_all("/area shape=\"(\w+)\" coords=\"([0-9\, ]+)\"/", $csim, $coords)) {
	    $this->img->SetColor($this->csimcolor);
	    $n = count($coords[0]);
	    for ($i=0; $i < $n; $i++) {
		if ($coords[1][$i]=="poly") {
		    preg_match_all('/\s*([0-9]+)\s*,\s*([0-9]+)\s*,*/',$coords[2][$i],$pts);
		    $this->img->SetStartPoint($pts[1][count($pts[0])-1],$pts[2][count($pts[0])-1]);
		    $m = count($pts[0]);
		    for ($j=0; $j < $m; $j++) {
			$this->img->LineTo($pts[1][$j],$pts[2][$j]);
		    }
		} else if ($coords[1][$i]=="rect") {
		    $pts = preg_split('/,/', $coords[2][$i]);
		    $this->img->SetStartPoint($pts[0],$pts[1]);
		    $this->img->LineTo($pts[2],$pts[1]);
		    $this->img->LineTo($pts[2],$pts[3]);
		    $this->img->LineTo($pts[0],$pts[3]);
		    $this->img->LineTo($pts[0],$pts[1]);					
		}
	    }
	}
    }

    // Text scale offset in world coordinates
    /**
     * SetTextScaleOff
     * Insert description here
     *
     * @param $aOff
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetTextScaleOff($aOff) {
	$this->text_scale_off = $aOff;
	$this->xscale->text_scale_off = $aOff;
    }

    // Text width of bar to be centered in absolute pixels
    /**
     * SetTextScaleAbsCenterOff
     * Insert description here
     *
     * @param $aOff
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetTextScaleAbsCenterOff($aOff) {
	$this->text_scale_abscenteroff = $aOff;
    }

    // Get Y min and max values for added lines
    /**
     * GetLinesYMinMax
     * Insert description here
     *
     * @param $aLines
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function GetLinesYMinMax( $aLines ) {
	$n = count($aLines);
	if( $n == 0 ) return false;
	$min = $aLines[0]->scaleposition ;
	$max = $min ;
	$flg = false;
	for( $i=0; $i < $n; ++$i ) {
	    if( $aLines[$i]->direction == HORIZONTAL ) {
		$flg = true ;
		$v = $aLines[$i]->scaleposition ;
		if( $min > $v ) $min = $v ;
		if( $max < $v ) $max = $v ;
	    }
	}
	return $flg ? array($min,$max) : false ;
    }

    // Get X min and max values for added lines
    /**
     * GetLinesXMinMax
     * Insert description here
     *
     * @param $aLines
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function GetLinesXMinMax( $aLines ) {
	$n = count($aLines);
	if( $n == 0 ) return false ;
	$min = $aLines[0]->scaleposition ;
	$max = $min ;
	$flg = false;
	for( $i=0; $i < $n; ++$i ) {
	    if( $aLines[$i]->direction == VERTICAL ) {
		$flg = true ;
		$v = $aLines[$i]->scaleposition ;
		if( $min > $v ) $min = $v ;
		if( $max < $v ) $max = $v ;
	    }
	}
	return $flg ? array($min,$max) : false ;
    }

    // Get min and max values for all included plots
    /**
     * GetPlotsYMinMax
     * Insert description here
     *
     * @param $aPlots
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function GetPlotsYMinMax($aPlots) {
	$n = count($aPlots);
	$i=0;
	do { 
	    list($xmax,$max) = $aPlots[$i]->Max();
	} while( ++$i < $n && !is_numeric($max) );

	$i=0;
	do { 
	    list($xmin,$min) = $aPlots[$i]->Min();
	} while( ++$i < $n && !is_numeric($min) );
	
	if( !is_numeric($min) || !is_numeric($max) ) {
	    JpGraphError::RaiseL(25044);//('Cannot use autoscaling since it is impossible to determine a valid min/max value  of the Y-axis (only null values).');
	}

	for($i=0; $i < $n; ++$i ) {
	    list($xmax,$ymax)=$aPlots[$i]->Max();
	    list($xmin,$ymin)=$aPlots[$i]->Min();
	    if (is_numeric($ymax)) $max=max($max,$ymax);
	    if (is_numeric($ymin)) $min=min($min,$ymin);
	}
	if( $min == '' ) $min = 0;
	if( $max == '' ) $max = 0;
	if( $min == 0 && $max == 0 ) {
	    // Special case if all values are 0
	    $min=0;$max=1;			
	}
	return array($min,$max);
    }

} // Class

//===================================================
// CLASS LineProperty
// Description: Holds properties for a line
//===================================================
/**
 * LineProperty
 * Insert description here
 *
 * @category
 * @package
 * @author
 * @copyright
 * @license
 * @version
 * @link
 * @see
 * @since
 */
class LineProperty {
    public $iWeight=1, $iColor="black",$iStyle="solid",$iShow=true;
	
//---------------
// PUBLIC METHODS	
    /**
     * SetColor
     * Insert description here
     *
     * @param $aColor
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetColor($aColor) {
	$this->iColor = $aColor;
    }
	
    /**
     * SetWeight
     * Insert description here
     *
     * @param $aWeight
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetWeight($aWeight) {
	$this->iWeight = $aWeight;
    }
	
    /**
     * SetStyle
     * Insert description here
     *
     * @param $aStyle
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetStyle($aStyle) {
	$this->iStyle = $aStyle;
    }
		
    /**
     * Show
     * Insert description here
     *
     * @param $aShow
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function Show($aShow=true) {
	$this->iShow=$aShow;
    }
	
    /**
     * Stroke
     * Insert description here
     *
     * @param $aImg
     * @param $aX1
     * @param $aY1
     * @param $aX2
     * @param $aY2
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function Stroke($aImg,$aX1,$aY1,$aX2,$aY2) {
	if( $this->iShow ) {
	    $aImg->PushColor($this->iColor);
	    $oldls = $aImg->line_style;
	    $oldlw = $aImg->line_weight;
	    $aImg->SetLineWeight($this->iWeight);
	    $aImg->SetLineStyle($this->iStyle);			
	    $aImg->StyleLine($aX1,$aY1,$aX2,$aY2);
	    $aImg->PopColor($this->iColor);
	    $aImg->line_style = $oldls;
	    $aImg->line_weight = $oldlw;

	}
    }
}


//===================================================
// CLASS Text
// Description: Arbitrary text object that can be added to the graph
//===================================================
/**
 * Text
 * Insert description here
 *
 * @category
 * @package
 * @author
 * @copyright
 * @license
 * @version
 * @link
 * @see
 * @since
 */
class Text {
    public $t,$margin=0;
    public $x=0,$y=0,$halign="left",$valign="top",$color=array(0,0,0);
    public $hide=false, $dir=0;
    public $iScalePosY=null,$iScalePosX=null;
    public $iWordwrap=0;
    public $font_family=FF_FONT1,$font_style=FS_NORMAL,$font_size=12;
    protected $boxed=false;	// Should the text be boxed
    protected $paragraph_align="left";
    protected $icornerradius=0,$ishadowwidth=3;
    protected $fcolor='white',$bcolor='black',$shadow=false;
    protected $iCSIMarea='',$iCSIMalt='',$iCSIMtarget='',$iCSIMWinTarget='';

//---------------
// CONSTRUCTOR

    // Create new text at absolute pixel coordinates
    /**
     * Text
     * Insert description here
     *
     * @param $aTxt
     * @param $aXAbsPos
     * @param $aYAbsPos
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function Text($aTxt="",$aXAbsPos=0,$aYAbsPos=0) {
	if( ! is_string($aTxt) ) {
	    JpGraphError::RaiseL(25050);//('First argument to Text::Text() must be s atring.');
	}
	$this->t = $aTxt;
	$this->x = round($aXAbsPos);
	$this->y = round($aYAbsPos);
	$this->margin = 0;
    }
//---------------
// PUBLIC METHODS	
    // Set the string in the text object
    /**
     * Set
     * Insert description here
     *
     * @param $aTxt
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function Set($aTxt) {
	$this->t = $aTxt;
    }
	
    // Alias for Pos()
    /**
     * SetPos
     * Insert description here
     *
     * @param $aXAbsPos
     * @param $aYAbsPos
     * @param $aHAlign
     * @param $aVAlign
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetPos($aXAbsPos=0,$aYAbsPos=0,$aHAlign="left",$aVAlign="top") {
	//$this->Pos($aXAbsPos,$aYAbsPos,$aHAlign,$aVAlign);
	$this->x = $aXAbsPos;
	$this->y = $aYAbsPos;
	$this->halign = $aHAlign;
	$this->valign = $aVAlign;
    }

    /**
     * SetScalePos
     * Insert description here
     *
     * @param $aX
     * @param $aY
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetScalePos($aX,$aY) {
	$this->iScalePosX = $aX;
	$this->iScalePosY = $aY;
    }
	
    // Specify alignment for the text
    /**
     * Align
     * Insert description here
     *
     * @param $aHAlign
     * @param $aVAlign
     * @param $aParagraphAlign
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function Align($aHAlign,$aVAlign="top",$aParagraphAlign="") {
	$this->halign = $aHAlign;
	$this->valign = $aVAlign;
	if( $aParagraphAlign != "" )
	    $this->paragraph_align = $aParagraphAlign;
    }		
    
    // Alias
    /**
     * SetAlign
     * Insert description here
     *
     * @param $aHAlign
     * @param $aVAlign
     * @param $aParagraphAlign
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetAlign($aHAlign,$aVAlign="top",$aParagraphAlign="") {
	$this->Align($aHAlign,$aVAlign,$aParagraphAlign);
    }

    // Specifies the alignment for a multi line text
    /**
     * ParagraphAlign
     * Insert description here
     *
     * @param $aAlign
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function ParagraphAlign($aAlign) {
	$this->paragraph_align = $aAlign;
    }

    // Specifies the alignment for a multi line text
    /**
     * SetParagraphAlign
     * Insert description here
     *
     * @param $aAlign
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetParagraphAlign($aAlign) {
	$this->paragraph_align = $aAlign;
    }

    /**
     * SetShadow
     * Insert description here
     *
     * @param $aShadowColor
     * @param $aShadowWidth
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetShadow($aShadowColor='gray',$aShadowWidth=3) {
	$this->ishadowwidth=$aShadowWidth;
	$this->shadow=$aShadowColor;
	$this->boxed=true;
    }

    /**
     * SetWordWrap
     * Insert description here
     *
     * @param $aCol
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetWordWrap($aCol) {
	$this->iWordwrap = $aCol ;
    }
	
    // Specify that the text should be boxed. fcolor=frame color, bcolor=border color,
    // $shadow=drop shadow should be added around the text.
    /**
     * SetBox
     * Insert description here
     *
     * @param $aFrameColor
     * @param 255
     * @param 255
     * @param 255
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetBox($aFrameColor=array(255,255,255),$aBorderColor=array(0,0,0),$aShadowColor=false,$aCornerRadius=4,$aShadowWidth=3) {
	if( $aFrameColor==false )
	    $this->boxed=false;
	else
	    $this->boxed=true;
	$this->fcolor=$aFrameColor;
	$this->bcolor=$aBorderColor;
	// For backwards compatibility when shadow was just true or false
	if( $aShadowColor === true )
	    $aShadowColor = 'gray';
	$this->shadow=$aShadowColor;
	$this->icornerradius=$aCornerRadius;
	$this->ishadowwidth=$aShadowWidth;
    }
	
    // Hide the text
    /**
     * Hide
     * Insert description here
     *
     * @param $aHide
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function Hide($aHide=true) {
	$this->hide=$aHide;
    }
	
    // This looks ugly since it's not a very orthogonal design 
    // but I added this "inverse" of Hide() to harmonize
    // with some classes which I designed more recently (especially) 
    // jpgraph_gantt
    /**
     * Show
     * Insert description here
     *
     * @param $aShow
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function Show($aShow=true) {
	$this->hide=!$aShow;
    }
	
    // Specify font
    /**
     * SetFont
     * Insert description here
     *
     * @param $aFamily
     * @param $aStyle
     * @param $aSize
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetFont($aFamily,$aStyle=FS_NORMAL,$aSize=10) {
	$this->font_family=$aFamily;
	$this->font_style=$aStyle;
	$this->font_size=$aSize;
    }
			
    // Center the text between $left and $right coordinates
    /**
     * Center
     * Insert description here
     *
     * @param $aLeft
     * @param $aRight
     * @param $aYAbsPos
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function Center($aLeft,$aRight,$aYAbsPos=false) {
	$this->x = $aLeft + ($aRight-$aLeft	)/2;
	$this->halign = "center";
	if( is_numeric($aYAbsPos) )
	    $this->y = $aYAbsPos;		
    }
	
    // Set text color
    /**
     * SetColor
     * Insert description here
     *
     * @param $aColor
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetColor($aColor) {
	$this->color = $aColor;
    }
	
    /**
     * SetAngle
     * Insert description here
     *
     * @param $aAngle
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetAngle($aAngle) {
	$this->SetOrientation($aAngle);
    }
	
    // Orientation of text. Note only TTF fonts can have an arbitrary angle
    /**
     * SetOrientation
     * Insert description here
     *
     * @param $aDirection
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetOrientation($aDirection=0) {
	if( is_numeric($aDirection) )
	    $this->dir=$aDirection;	
	elseif( $aDirection=="h" )
	    $this->dir = 0;
	elseif( $aDirection=="v" )
	    $this->dir = 90;
	else JpGraphError::RaiseL(25051);//(" Invalid direction specified for text.");
    }
	
    // Total width of text
    /**
     * GetWidth
     * Insert description here
     *
     * @param $aImg
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function GetWidth($aImg) {
	$aImg->SetFont($this->font_family,$this->font_style,$this->font_size);
	$w = $aImg->GetTextWidth($this->t,$this->dir);
	return $w;	
    }
	
    // Hight of font
    /**
     * GetFontHeight
     * Insert description here
     *
     * @param $aImg
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function GetFontHeight($aImg) {
	$aImg->SetFont($this->font_family,$this->font_style,$this->font_size);
	$h = $aImg->GetFontHeight();
	return $h;

    }

    /**
     * GetTextHeight
     * Insert description here
     *
     * @param $aImg
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function GetTextHeight($aImg) {
	$aImg->SetFont($this->font_family,$this->font_style,$this->font_size);	
	$h = $aImg->GetTextHeight($this->t,$this->dir);
	return $h;
    }

    /**
     * GetHeight
     * Insert description here
     *
     * @param $aImg
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function GetHeight($aImg) {
	// Synonym for GetTextHeight()
	$aImg->SetFont($this->font_family,$this->font_style,$this->font_size);	
	$h = $aImg->GetTextHeight($this->t,$this->dir);
	return $h;
    }

    // Set the margin which will be interpretated differently depending
    // on the context.
    /**
     * SetMargin
     * Insert description here
     *
     * @param $aMarg
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetMargin($aMarg) {
	$this->margin = $aMarg;
    }

    /**
     * StrokeWithScale
     * Insert description here
     *
     * @param $aImg
     * @param $axscale
     * @param $ayscale
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function StrokeWithScale($aImg,$axscale,$ayscale) {
	if( $this->iScalePosX === null ||
	    $this->iScalePosY === null ) {
	    $this->Stroke($aImg);
	}
	else {
	    $this->Stroke($aImg,
			  round($axscale->Translate($this->iScalePosX)),
			  round($ayscale->Translate($this->iScalePosY)));
	}
    }

    /**
     * SetCSIMTarget
     * Insert description here
     *
     * @param $aURITarget
     * @param $aAlt
     * @param $aWinTarget
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetCSIMTarget($aURITarget,$aAlt='',$aWinTarget='') {
	$this->iCSIMtarget = $aURITarget;
	$this->iCSIMalt = $aAlt;
	$this->iCSIMWinTarget = $aWinTarget;
    }

    /**
     * GetCSIMareas
     * Insert description here
     *
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function GetCSIMareas() {
	if( $this->iCSIMtarget !== '' ) 
	    return $this->iCSIMarea;
	else
	    return '';
    }

    // Display text in image
    /**
     * Stroke
     * Insert description here
     *
     * @param $aImg
     * @param $x
     * @param $y
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function Stroke($aImg,$x=null,$y=null) {

	if( !empty($x) ) $this->x = round($x);
	if( !empty($y) ) $this->y = round($y);

	// Insert newlines
	if( $this->iWordwrap > 0 ) {
	    $this->t = wordwrap($this->t,$this->iWordwrap,"\n");
	}

	// If position been given as a fraction of the image size
	// calculate the absolute position
	if( $this->x < 1 && $this->x > 0 ) $this->x *= $aImg->width;
	if( $this->y < 1 && $this->y > 0 ) $this->y *= $aImg->height;

	$aImg->PushColor($this->color);	
	$aImg->SetFont($this->font_family,$this->font_style,$this->font_size);
	$aImg->SetTextAlign($this->halign,$this->valign);
	if( $this->boxed ) {
	    if( $this->fcolor=="nofill" ) 
		$this->fcolor=false;		
	    $aImg->SetLineWeight(1);
	    $bbox = $aImg->StrokeBoxedText($this->x,$this->y,$this->t,
				   $this->dir,$this->fcolor,$this->bcolor,$this->shadow,
				   $this->paragraph_align,5,5,$this->icornerradius,
				   $this->ishadowwidth);
	}
	else {
	    $bbox = $aImg->StrokeText($this->x,$this->y,$this->t,$this->dir,$this->paragraph_align);
	}

	// Create CSIM targets
	$coords = $bbox[0].','.$bbox[1].','.$bbox[2].','.$bbox[3].','.$bbox[4].','.$bbox[5].','.$bbox[6].','.$bbox[7];
	$this->iCSIMarea = "<area shape=\"poly\" coords=\"$coords\" href=\"".htmlentities($this->iCSIMtarget)."\" ";
	if( trim($this->iCSIMalt) != '' ) {
	    $this->iCSIMarea .= " alt=\"".$this->iCSIMalt."\" "; 
	    $this->iCSIMarea .= " title=\"".$this->iCSIMalt."\" ";
	}
	if( trim($this->iCSIMWinTarget) != '' ) {
	    $this->iCSIMarea .= " target=\"".$this->iCSIMWinTarget."\" "; 
	}
	$this->iCSIMarea .= " />\n";

	$aImg->PopColor($this->color);	

    }
} // Class

/**
 * GraphTabTitle
 * Insert description here
 *
 * @category
 * @package
 * @author
 * @copyright
 * @license
 * @version
 * @link
 * @see
 * @since
 */
class GraphTabTitle extends Text{
    private $corner = 6 , $posx = 7, $posy = 4;
    private $fillcolor='lightyellow',$bordercolor='black';
    private $align = 'left', $width=TABTITLE_WIDTHFIT;
    /**
     * GraphTabTitle
     * Insert description here
     *
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function GraphTabTitle() {
	$this->t = '';
	$this->font_style = FS_BOLD;
	$this->hide = true;
	$this->color = 'darkred';
    }

    /**
     * SetColor
     * Insert description here
     *
     * @param $aTxtColor
     * @param $aFillColor
     * @param $aBorderColor
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetColor($aTxtColor,$aFillColor='lightyellow',$aBorderColor='black') {
	$this->color = $aTxtColor;
	$this->fillcolor = $aFillColor;
	$this->bordercolor = $aBorderColor;
    }

    /**
     * SetFillColor
     * Insert description here
     *
     * @param $aFillColor
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetFillColor($aFillColor) {
	$this->fillcolor = $aFillColor;
    }

    /**
     * SetTabAlign
     * Insert description here
     *
     * @param $aAlign
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetTabAlign($aAlign) {
	$this->align = $aAlign;
    }
    
    /**
     * SetWidth
     * Insert description here
     *
     * @param $aWidth
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetWidth($aWidth) {
	$this->width = $aWidth ;
    }

    /**
     * Set
     * Insert description here
     *
     * @param $t
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function Set($t) {
	$this->t = $t;
	$this->hide = false;
    }

    /**
     * SetCorner
     * Insert description here
     *
     * @param $aD
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetCorner($aD) {
	$this->corner = $aD ;
    }

    /**
     * Stroke
     * Insert description here
     *
     * @param $aImg
     * @param $aDummy1
     * @param $aDummy2
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function Stroke($aImg,$aDummy1=null,$aDummy2=null) {
	if( $this->hide ) 
	    return;
	$this->boxed = false;
	$w = $this->GetWidth($aImg) + 2*$this->posx;
	$h = $this->GetTextHeight($aImg) + 2*$this->posy;

	$x = $aImg->left_margin;
	$y = $aImg->top_margin;

	if( $this->width === TABTITLE_WIDTHFIT ) {
	    if( $this->align == 'left' ) {
		$p = array($x,                $y,
			   $x,                $y-$h+$this->corner,
			   $x + $this->corner,$y-$h,
			   $x + $w - $this->corner, $y-$h,
			   $x + $w, $y-$h+$this->corner,
			   $x + $w, $y);
	    }
	    elseif( $this->align == 'center' ) {
		$x += round($aImg->plotwidth/2) - round($w/2);
		$p = array($x, $y,
			   $x, $y-$h+$this->corner,
			   $x + $this->corner, $y-$h,
			   $x + $w - $this->corner, $y-$h,
			   $x + $w, $y-$h+$this->corner,
			   $x + $w, $y);
	    }
	    else {
		$x += $aImg->plotwidth -$w;
		$p = array($x, $y,
			   $x, $y-$h+$this->corner,
			   $x + $this->corner,$y-$h,
			   $x + $w - $this->corner, $y-$h,
			   $x + $w, $y-$h+$this->corner,
			   $x + $w, $y);
	    }
	}
	else {
	    if( $this->width === TABTITLE_WIDTHFULL )
		$w = $aImg->plotwidth ;
	    else
		$w = $this->width ;

	    // Make the tab fit the width of the plot area
	    $p = array($x,                $y,
		       $x,                $y-$h+$this->corner,
		       $x + $this->corner,$y-$h,
		       $x + $w - $this->corner, $y-$h,
		       $x + $w, $y-$h+$this->corner,
		       $x + $w, $y);
	    
	}
	if( $this->halign == 'left' ) {
	    $aImg->SetTextAlign('left','bottom');
	    $x += $this->posx;
	    $y -= $this->posy;
	}
	elseif( $this->halign == 'center' ) {
	    $aImg->SetTextAlign('center','bottom');
	    $x += $w/2; 
	    $y -= $this->posy;
	}
	else {
	    $aImg->SetTextAlign('right','bottom');
	    $x += $w - $this->posx;
	    $y -= $this->posy;
	}

	$aImg->SetColor($this->fillcolor);
	$aImg->FilledPolygon($p);

	$aImg->SetColor($this->bordercolor);
	$aImg->Polygon($p,true);
	
	$aImg->SetColor($this->color);
	$aImg->SetFont($this->font_family,$this->font_style,$this->font_size);
	$aImg->StrokeText($x,$y,$this->t,0,'center');
    }

}

//===================================================
// CLASS SuperScriptText
// Description: Format a superscript text
//===================================================
/**
 * SuperScriptText
 * Insert description here
 *
 * @category
 * @package
 * @author
 * @copyright
 * @license
 * @version
 * @link
 * @see
 * @since
 */
class SuperScriptText extends Text {
    private $iSuper="";
    private $sfont_family="",$sfont_style="",$sfont_size=8;
    private $iSuperMargin=2,$iVertOverlap=4,$iSuperScale=0.65;
    private $iSDir=0;
    private $iSimple=false;

    /**
     * SuperScriptText
     * Insert description here
     *
     * @param $aTxt
     * @param $aSuper
     * @param $aXAbsPos
     * @param $aYAbsPos
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SuperScriptText($aTxt="",$aSuper="",$aXAbsPos=0,$aYAbsPos=0) {
	parent::Text($aTxt,$aXAbsPos,$aYAbsPos);
	$this->iSuper = $aSuper;
    }

    /**
     * FromReal
     * Insert description here
     *
     * @param $aVal
     * @param $aPrecision
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function FromReal($aVal,$aPrecision=2) {
	// Convert a floating point number to scientific notation
	$neg=1.0;
	if( $aVal < 0 ) {
	    $neg = -1.0;
	    $aVal = -$aVal;
	}
		
	$l = floor(log10($aVal));
	$a = sprintf("%0.".$aPrecision."f",round($aVal / pow(10,$l),$aPrecision));
	$a *= $neg;
	if( $this->iSimple && ($a == 1 || $a==-1) ) $a = '';
	
	if( $a != '' )
	    $this->t = $a.' * 10';
	else {
	    if( $neg == 1 )
		$this->t = '10';
	    else
		$this->t = '-10';
	}
	$this->iSuper = $l;
    }

    /**
     * Set
     * Insert description here
     *
     * @param $aTxt
     * @param $aSuper
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function Set($aTxt,$aSuper="") {
	$this->t = $aTxt;
	$this->iSuper = $aSuper;
    }

    /**
     * SetSuperFont
     * Insert description here
     *
     * @param $aFontFam
     * @param $aFontStyle
     * @param $aFontSize
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetSuperFont($aFontFam,$aFontStyle=FS_NORMAL,$aFontSize=8) {
	$this->sfont_family = $aFontFam;
	$this->sfont_style = $aFontStyle;
	$this->sfont_size = $aFontSize;
    }

    // Total width of text
    /**
     * GetWidth
     * Insert description here
     *
     * @param $aImg
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function GetWidth($aImg) {
	$aImg->SetFont($this->font_family,$this->font_style,$this->font_size);
	$w = $aImg->GetTextWidth($this->t);
	$aImg->SetFont($this->sfont_family,$this->sfont_style,$this->sfont_size);
	$w += $aImg->GetTextWidth($this->iSuper);
	$w += $this->iSuperMargin;
	return $w;
    }
	
    // Hight of font (approximate the height of the text)
    /**
     * GetFontHeight
     * Insert description here
     *
     * @param $aImg
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function GetFontHeight($aImg) {
	$aImg->SetFont($this->font_family,$this->font_style,$this->font_size);	
	$h = $aImg->GetFontHeight();
	$aImg->SetFont($this->sfont_family,$this->sfont_style,$this->sfont_size);
	$h += $aImg->GetFontHeight();
	return $h;
    }

    // Hight of text
    /**
     * GetTextHeight
     * Insert description here
     *
     * @param $aImg
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function GetTextHeight($aImg) {
	$aImg->SetFont($this->font_family,$this->font_style,$this->font_size);
	$h = $aImg->GetTextHeight($this->t);
	$aImg->SetFont($this->sfont_family,$this->sfont_style,$this->sfont_size);
	$h += $aImg->GetTextHeight($this->iSuper);
	return $h;
    }

    /**
     * Stroke
     * Insert description here
     *
     * @param $aImg
     * @param $ax
     * @param 1
     * @param $ay
     * @param 1
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function Stroke($aImg,$ax=-1,$ay=-1) {
	
        // To position the super script correctly we need different
	// cases to handle the alignmewnt specified since that will
	// determine how we can interpret the x,y coordinates
	
	$w = parent::GetWidth($aImg);
	$h = parent::GetTextHeight($aImg);
	switch( $this->valign ) {
	    case 'top':
		$sy = $this->y;
		break;
	    case 'center':
		$sy = $this->y - $h/2;
		break;
	    case 'bottom':
		$sy = $this->y - $h;
		break;
	    default:
		JpGraphError::RaiseL(25052);//('PANIC: Internal error in SuperScript::Stroke(). Unknown vertical alignment for text');
		break;
	}

	switch( $this->halign ) {
	    case 'left':
		$sx = $this->x + $w;
		break;
	    case 'center':
		$sx = $this->x + $w/2;
		break;
	    case 'right':
		$sx = $this->x;
		break;
	    default:
		JpGraphError::RaiseL(25053);//('PANIC: Internal error in SuperScript::Stroke(). Unknown horizontal alignment for text');
		break;
	}

	$sx += $this->iSuperMargin;
	$sy += $this->iVertOverlap;

	// Should we automatically determine the font or
	// has the user specified it explicetly?
	if( $this->sfont_family == "" ) {
	    if( $this->font_family <= FF_FONT2 ) {
		if( $this->font_family == FF_FONT0 ) {
		    $sff = FF_FONT0;
		}
		elseif( $this->font_family == FF_FONT1 ) {
		    if( $this->font_style == FS_NORMAL )
			$sff = FF_FONT0;
		    else
			$sff = FF_FONT1;
		}
		else {
		    $sff = FF_FONT1;
		}
		$sfs = $this->font_style;
		$sfz = $this->font_size;
	    }
	    else {
		// TTF fonts
		$sff = $this->font_family;
		$sfs = $this->font_style;
		$sfz = floor($this->font_size*$this->iSuperScale);		
		if( $sfz < 8 ) $sfz = 8;
	    }	    
	    $this->sfont_family = $sff;
	    $this->sfont_style = $sfs;
	    $this->sfont_size = $sfz;	    
	} 
	else {
	    $sff = $this->sfont_family;
	    $sfs = $this->sfont_style;
	    $sfz = $this->sfont_size;	    
	}

	parent::Stroke($aImg,$ax,$ay);


	// For the builtin fonts we need to reduce the margins
	// since the bounding bx reported for the builtin fonts
	// are much larger than for the TTF fonts.
	if( $sff <= FF_FONT2 ) {
	    $sx -= 2;
	    $sy += 3;
	}

	$aImg->SetTextAlign('left','bottom');	
	$aImg->SetFont($sff,$sfs,$sfz);
	$aImg->PushColor($this->color);	
	$aImg->StrokeText($sx,$sy,$this->iSuper,$this->iSDir,'left');
	$aImg->PopColor();	
    }
}


//===================================================
// CLASS Grid
// Description: responsible for drawing grid lines in graph
//===================================================
/**
 * Grid
 * Insert description here
 *
 * @category
 * @package
 * @author
 * @copyright
 * @license
 * @version
 * @link
 * @see
 * @since
 */
class Grid {
    protected $img;
    protected $scale;
    protected $grid_color='#DDDDDD',$grid_mincolor='#DDDDDD';
    protected $type="solid";
    protected $show=false, $showMinor=false,$weight=1;
    protected $fill=false,$fillcolor=array('#EFEFEF','#BBCCFF');
//---------------
// CONSTRUCTOR
    /**
     * Grid
     * Insert description here
     *
     * @param $aAxis
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function Grid($aAxis) {
	$this->scale = $aAxis->scale;
	$this->img = $aAxis->img;
    }
//---------------
// PUBLIC METHODS
    /**
     * SetColor
     * Insert description here
     *
     * @param $aMajColor
     * @param $aMinColor
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetColor($aMajColor,$aMinColor=false) {
	$this->grid_color=$aMajColor;
	if( $aMinColor === false ) 
	    $aMinColor = $aMajColor ;
	$this->grid_mincolor = $aMinColor;
    }
	
    /**
     * SetWeight
     * Insert description here
     *
     * @param $aWeight
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetWeight($aWeight) {
	$this->weight=$aWeight;
    }
	
    // Specify if grid should be dashed, dotted or solid
    /**
     * SetLineStyle
     * Insert description here
     *
     * @param $aType
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetLineStyle($aType) {
	$this->type = $aType;
    }
	
    // Decide if both major and minor grid should be displayed
    /**
     * Show
     * Insert description here
     *
     * @param $aShowMajor
     * @param $aShowMinor
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function Show($aShowMajor=true,$aShowMinor=false) {
	$this->show=$aShowMajor;
	$this->showMinor=$aShowMinor;
    }
    
    /**
     * SetFill
     * Insert description here
     *
     * @param $aFlg
     * @param $aColor1
     * @param $aColor2
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetFill($aFlg=true,$aColor1='lightgray',$aColor2='lightblue') {
	$this->fill = $aFlg;
	$this->fillcolor = array( $aColor1, $aColor2 );
    }
	
    // Display the grid
    /**
     * Stroke
     * Insert description here
     *
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function Stroke() {
	if( $this->showMinor && !$this->scale->textscale ) {
	    $tmp = $this->grid_color;
	    $this->grid_color = $this->grid_mincolor;
	    $this->DoStroke($this->scale->ticks->ticks_pos);

	    $this->grid_color = $tmp;
	    $this->DoStroke($this->scale->ticks->maj_ticks_pos);
	}
	else {
	    $this->DoStroke($this->scale->ticks->maj_ticks_pos);
	}
    }
	
//--------------
// Private methods	
    // Draw the grid
    /**
     * DoStroke
     * Insert description here
     *
     * @param $aTicksPos
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function DoStroke($aTicksPos) {
	if( !$this->show )
	    return;	
	$nbrgrids = count($aTicksPos);	

	if( $this->scale->type=="y" ) {
	    $xl=$this->img->left_margin;
	    $xr=$this->img->width-$this->img->right_margin;
	    
	    if( $this->fill ) {
		// Draw filled areas
		$y2 = $aTicksPos[0];
		$i=1;
		while( $i < $nbrgrids ) {
		    $y1 = $y2;
		    $y2 = $aTicksPos[$i++];
		    $this->img->SetColor($this->fillcolor[$i & 1]);
		    $this->img->FilledRectangle($xl,$y1,$xr,$y2);
		}
	    }

	    $this->img->SetColor($this->grid_color);
	    $this->img->SetLineWeight($this->weight);

	    // Draw grid lines
	    switch( $this->type ) {
		case "solid":  $style = LINESTYLE_SOLID; break;
		case "dotted": $style = LINESTYLE_DOTTED; break;
		case "dashed": $style = LINESTYLE_DASHED; break;
		case "longdashed": $style = LINESTYLE_LONGDASH; break;
		default:
		    $style = LINESTYLE_SOLID; break;
	    }

	    for($i=0; $i < $nbrgrids; ++$i) {
		$y=$aTicksPos[$i];
		$this->img->StyleLine($xl,$y,$xr,$y,$style);
	    }
	}
	elseif( $this->scale->type=="x" ) {	
	    $yu=$this->img->top_margin;
	    $yl=$this->img->height-$this->img->bottom_margin;
	    $limit=$this->img->width-$this->img->right_margin;

	    if( $this->fill ) {
		// Draw filled areas
		$x2 = $aTicksPos[0];
		$i=1;
		while( $i < $nbrgrids ) {
		    $x1 = $x2;
		    $x2 = min($aTicksPos[$i++],$limit) ;
		    $this->img->SetColor($this->fillcolor[$i & 1]);
		    $this->img->FilledRectangle($x1,$yu,$x2,$yl);
		}
	    }

	    $this->img->SetColor($this->grid_color);
	    $this->img->SetLineWeight($this->weight);

	    // We must also test for limit since we might have
	    // an offset and the number of ticks is calculated with
	    // assumption offset==0 so we might end up drawing one
	    // to many gridlines
	    $i=0;
	    $x=$aTicksPos[$i];	    
	    while( $i<count($aTicksPos) && ($x=$aTicksPos[$i]) <= $limit ) {
		if( $this->type == "solid" )				
		    $this->img->Line($x,$yl,$x,$yu);
		elseif( $this->type == "dotted" )
		    $this->img->DashedLine($x,$yl,$x,$yu,1,6);
		elseif( $this->type == "dashed" )
		    $this->img->DashedLine($x,$yl,$x,$yu,2,4);
		elseif( $this->type == "longdashed" )
		    $this->img->DashedLine($x,$yl,$x,$yu,8,6);	
		++$i;  
	    }
	}	
	else {
	    JpGraphError::RaiseL(25054,$this->scale->type);//('Internal error: Unknown grid axis ['.$this->scale->type.']');
	}
	return true;
    }
} // Class

//===================================================
// CLASS Axis
// Description: Defines X and Y axis. Notes that at the
// moment the code is not really good since the axis on
// several occasion must know wheter it's an X or Y axis.
// This was a design decision to make the code easier to
// follow. 
//===================================================
/**
 * AxisPrototype
 * Insert description here
 *
 * @category
 * @package
 * @author
 * @copyright
 * @license
 * @version
 * @link
 * @see
 * @since
 */
class AxisPrototype {
    public $scale=null; 
    public $img=null;
    public $hide=false,$hide_labels=false;
    public $title=null;
    public $font_family=FF_FONT1,$font_style=FS_NORMAL,$font_size=12,$label_angle=0;
    public $tick_step=1;
    public $pos = false;
    public $ticks_label = array();

    protected $weight=1;
    protected $color=array(0,0,0),$label_color=array(0,0,0);
    protected $ticks_label_colors=null;
    protected $show_first_label=true,$show_last_label=true;
    protected $label_step=1; // Used by a text axis to specify what multiple of major steps
    // should be labeled.
    protected $labelPos=0;   // Which side of the axis should the labels be?
    protected $title_adjust,$title_margin,$title_side=SIDE_LEFT;
    protected $tick_label_margin=7;
    protected $label_halign = '',$label_valign = '', $label_para_align='left';
    protected $hide_line=false;
    protected $iDeltaAbsPos=0;

//---------------
// CONSTRUCTOR
    /**
     * Axis
     * Insert description here
     *
     * @param $img
     * @param $aScale
     * @param $color
     * @param 0
     * @param 0
     * @param 0
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function Axis($img,$aScale,$color=array(0,0,0)) {
	$this->img = $img;
	$this->scale = $aScale;
	$this->color = $color;
	$this->title=new Text("");
		
	if( $aScale->type=="y" ) {
	    $this->title_margin = 25;
	    $this->title_adjust="middle";
	    $this->title->SetOrientation(90);
	    $this->tick_label_margin=7;
	    $this->labelPos=SIDE_LEFT;
	}
	else {
	    $this->title_margin = 5;
	    $this->title_adjust="high";
	    $this->title->SetOrientation(0);			
	    $this->tick_label_margin=7;
	    $this->labelPos=SIDE_DOWN;
	    $this->title_side=SIDE_DOWN;
	}
    }
//---------------
// PUBLIC METHODS	
	
    /**
     * SetLabelFormat
     * Insert description here
     *
     * @param $aFormStr
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetLabelFormat($aFormStr) {
	$this->scale->ticks->SetLabelFormat($aFormStr);
    }

    /**
     * SetLabelFormatString
     * Insert description here
     *
     * @param $aFormStr
     * @param $aDate
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetLabelFormatString($aFormStr,$aDate=false) {
	$this->scale->ticks->SetLabelFormat($aFormStr,$aDate);
    }
	
    /**
     * SetLabelFormatCallback
     * Insert description here
     *
     * @param $aFuncName
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetLabelFormatCallback($aFuncName) {
	$this->scale->ticks->SetFormatCallback($aFuncName);
    }

    /**
     * SetLabelAlign
     * Insert description here
     *
     * @param $aHAlign
     * @param $aVAlign
     * @param $aParagraphAlign
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetLabelAlign($aHAlign,$aVAlign="top",$aParagraphAlign='left') {
	$this->label_halign = $aHAlign;
	$this->label_valign = $aVAlign;
	$this->label_para_align = $aParagraphAlign;
    }		

    // Don't display the first label
    /**
     * HideFirstTickLabel
     * Insert description here
     *
     * @param $aShow
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function HideFirstTickLabel($aShow=false) {
	$this->show_first_label=$aShow;
    }

    /**
     * HideLastTickLabel
     * Insert description here
     *
     * @param $aShow
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function HideLastTickLabel($aShow=false) {
	$this->show_last_label=$aShow;
    }

    // Manually specify the major and (optional) minor tick position and labels
    /**
     * SetTickPositions
     * Insert description here
     *
     * @param $aMajPos
     * @param $aMinPos
     * @param $aLabels
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetTickPositions($aMajPos,$aMinPos=NULL,$aLabels=NULL) {
	$this->scale->ticks->SetTickPositions($aMajPos,$aMinPos,$aLabels);
    }

    // Manually specify major tick positions and optional labels
    /**
     * SetMajTickPositions
     * Insert description here
     *
     * @param $aMajPos
     * @param $aLabels
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetMajTickPositions($aMajPos,$aLabels=NULL) {
	$this->scale->ticks->SetTickPositions($aMajPos,NULL,$aLabels);
    }

    // Hide minor or major tick marks
    /**
     * HideTicks
     * Insert description here
     *
     * @param $aHideMinor
     * @param $aHideMajor
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function HideTicks($aHideMinor=true,$aHideMajor=true) {
	$this->scale->ticks->SupressMinorTickMarks($aHideMinor);
	$this->scale->ticks->SupressTickMarks($aHideMajor);
    }

    // Hide zero label
    /**
     * HideZeroLabel
     * Insert description here
     *
     * @param $aFlag
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function HideZeroLabel($aFlag=true) {
	$this->scale->ticks->SupressZeroLabel();
    }
	
    /**
     * HideFirstLastLabel
     * Insert description here
     *
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function HideFirstLastLabel() {
	// The two first calls to ticks method will supress 
	// automatically generated scale values. However, that
	// will not affect manually specified value, e.g text-scales.
	// therefor we also make a kludge here to supress manually
	// specified scale labels.
	$this->scale->ticks->SupressLast();
	$this->scale->ticks->SupressFirst();
	$this->show_first_label	= false;
	$this->show_last_label = false;
    }
	
    // Hide the axis
    /**
     * Hide
     * Insert description here
     *
     * @param $aHide
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function Hide($aHide=true) {
	$this->hide=$aHide;
    }

    // Hide the actual axis-line, but still print the labels
    /**
     * HideLine
     * Insert description here
     *
     * @param $aHide
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function HideLine($aHide=true) {
	$this->hide_line = $aHide;
    }

    /**
     * HideLabels
     * Insert description here
     *
     * @param $aHide
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function HideLabels($aHide=true) {
	$this->hide_labels = $aHide;
    }
    

    // Weight of axis
    /**
     * SetWeight
     * Insert description here
     *
     * @param $aWeight
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetWeight($aWeight) {
	$this->weight = $aWeight;
    }

    // Axis color
    /**
     * SetColor
     * Insert description here
     *
     * @param $aColor
     * @param $aLabelColor
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetColor($aColor,$aLabelColor=false) {
	$this->color = $aColor;
	if( !$aLabelColor ) $this->label_color = $aColor;
	else $this->label_color = $aLabelColor;
    }
	
    // Title on axis
    /**
     * SetTitle
     * Insert description here
     *
     * @param $aTitle
     * @param $aAdjustAlign
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetTitle($aTitle,$aAdjustAlign="high") {
	$this->title->Set($aTitle);
	$this->title_adjust=$aAdjustAlign;
    }
	
    // Specify distance from the axis
    /**
     * SetTitleMargin
     * Insert description here
     *
     * @param $aMargin
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetTitleMargin($aMargin) {
	$this->title_margin=$aMargin;
    }
	
    // Which side of the axis should the axis title be?
    /**
     * SetTitleSide
     * Insert description here
     *
     * @param $aSideOfAxis
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetTitleSide($aSideOfAxis) {
	$this->title_side = $aSideOfAxis;
    }

    // Utility function to set the direction for tick marks
    /**
     * SetTickDirection
     * Insert description here
     *
     * @param $aDir
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetTickDirection($aDir) {
    	// Will be deprecated from 1.7    	
    	if( ERR_DEPRECATED )
	    JpGraphError::RaiseL(25055);//('Axis::SetTickDirection() is deprecated. Use Axis::SetTickSide() instead');
	$this->scale->ticks->SetSide($aDir);
    }
    
    /**
     * SetTickSide
     * Insert description here
     *
     * @param $aDir
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetTickSide($aDir) {
	$this->scale->ticks->SetSide($aDir);
    }
	
    // Specify text labels for the ticks. One label for each data point
    /**
     * SetTickLabels
     * Insert description here
     *
     * @param $aLabelArray
     * @param $aLabelColorArray
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetTickLabels($aLabelArray,$aLabelColorArray=null) {
	$this->ticks_label = $aLabelArray;
	$this->ticks_label_colors = $aLabelColorArray;
    }
	
    // How far from the axis should the labels be drawn
    /**
     * SetTickLabelMargin
     * Insert description here
     *
     * @param $aMargin
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetTickLabelMargin($aMargin) {
	if( ERR_DEPRECATED )    	
	    JpGraphError::RaiseL(25056);//('SetTickLabelMargin() is deprecated. Use Axis::SetLabelMargin() instead.');
      	$this->tick_label_margin=$aMargin;
    }

    /**
     * SetLabelMargin
     * Insert description here
     *
     * @param $aMargin
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetLabelMargin($aMargin) {
	$this->tick_label_margin=$aMargin;
    }
	
    // Specify that every $step of the ticks should be displayed starting
    // at $start
    // DEPRECATED FUNCTION: USE SetTextTickInterval() INSTEAD
    /**
     * SetTextTicks
     * Insert description here
     *
     * @param $step
     * @param $start
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetTextTicks($step,$start=0) {
	JpGraphError::RaiseL(25057);//(" SetTextTicks() is deprecated. Use SetTextTickInterval() instead.");		
    }

    // Specify that every $step of the ticks should be displayed starting
    // at $start	
    /**
     * SetTextTickInterval
     * Insert description here
     *
     * @param $aStep
     * @param $aStart
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetTextTickInterval($aStep,$aStart=0) {
	$this->scale->ticks->SetTextLabelStart($aStart);
	$this->tick_step=$aStep;
    }
	 
    // Specify that every $step tick mark should have a label 
    // should be displayed starting
    /**
     * SetTextLabelInterval
     * Insert description here
     *
     * @param $aStep
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetTextLabelInterval($aStep) {
	if( $aStep < 1 )
	    JpGraphError::RaiseL(25058);//(" Text label interval must be specified >= 1.");
	$this->label_step=$aStep;
    }
	
    // Which side of the axis should the labels be on?
    /**
     * SetLabelPos
     * Insert description here
     *
     * @param $aSidePos
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetLabelPos($aSidePos) {
    	// This will be deprecated from 1.7
	if( ERR_DEPRECATED )    	
	    JpGraphError::RaiseL(25059);//('SetLabelPos() is deprecated. Use Axis::SetLabelSide() instead.');
	$this->labelPos=$aSidePos;
    }
    
    /**
     * SetLabelSide
     * Insert description here
     *
     * @param $aSidePos
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetLabelSide($aSidePos) {
	$this->labelPos=$aSidePos;
    }

    // Set the font
    /**
     * SetFont
     * Insert description here
     *
     * @param $aFamily
     * @param $aStyle
     * @param $aSize
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetFont($aFamily,$aStyle=FS_NORMAL,$aSize=10) {
	$this->font_family = $aFamily;
	$this->font_style = $aStyle;
	$this->font_size = $aSize;
    }

    // Position for axis line on the "other" scale
    /**
     * SetPos
     * Insert description here
     *
     * @param $aPosOnOtherScale
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetPos($aPosOnOtherScale) {
	$this->pos=$aPosOnOtherScale;
    }

    // Set the position of the axis to be X-pixels delta to the right 
    // of the max X-position (used to position the multiple Y-axis)
    /**
     * SetPosAbsDelta
     * Insert description here
     *
     * @param $aDelta
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetPosAbsDelta($aDelta) {
      $this->iDeltaAbsPos=$aDelta;
    }
	
    // Specify the angle for the tick labels
    /**
     * SetLabelAngle
     * Insert description here
     *
     * @param $aAngle
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetLabelAngle($aAngle) {
	$this->label_angle = $aAngle;
    }	

} // Class


//===================================================
// CLASS Axis
// Description: Defines X and Y axis. Notes that at the
// moment the code is not really good since the axis on
// several occasion must know wheter it's an X or Y axis.
// This was a design decision to make the code easier to
// follow. 
//===================================================
/**
 * Axis
 * Insert description here
 *
 * @category
 * @package
 * @author
 * @copyright
 * @license
 * @version
 * @link
 * @see
 * @since
 */
class Axis extends AxisPrototype {

    /**
     * Axis
     * Insert description here
     *
     * @param $img
     * @param $aScale
     * @param $color
     * @param 0
     * @param 0
     * @param 0
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function Axis($img,$aScale,$color=array(0,0,0)) {
	parent::Axis($img,$aScale,$color);
    }
	
    // Stroke the axis.
    /**
     * Stroke
     * Insert description here
     *
     * @param $aOtherAxisScale
     * @param $aStrokeLabels
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function Stroke($aOtherAxisScale,$aStrokeLabels=true) {		
	if( $this->hide ) return;		
	if( is_numeric($this->pos) ) {
	    $pos=$aOtherAxisScale->Translate($this->pos);
	}
	else {	// Default to minimum of other scale if pos not set		
	    if( ($aOtherAxisScale->GetMinVal() >= 0 && $this->pos==false) || $this->pos=="min" ) {
		$pos = $aOtherAxisScale->scale_abs[0];
	    }
	    elseif($this->pos == "max") {
		$pos = $aOtherAxisScale->scale_abs[1];
	    }
	    else { // If negative set x-axis at 0
		$this->pos=0;
		$pos=$aOtherAxisScale->Translate(0);
	    }
	}
	$pos += $this->iDeltaAbsPos;	
	$this->img->SetLineWeight($this->weight);
	$this->img->SetColor($this->color);		
	$this->img->SetFont($this->font_family,$this->font_style,$this->font_size);
	if( $this->scale->type == "x" ) {
	    if( !$this->hide_line ) 
		$this->img->FilledRectangle($this->img->left_margin,$pos,
					    $this->img->width-$this->img->right_margin,$pos+$this->weight-1);
	    if( $this->title_side == SIDE_DOWN ) {
		$y = $pos + $this->img->GetFontHeight() + $this->title_margin + $this->title->margin;
		$yalign = 'top';
	    }
	    else {
		$y = $pos - $this->img->GetFontHeight() - $this->title_margin - $this->title->margin;
		$yalign = 'bottom';
	    }

	    if( $this->title_adjust=='high' )
		$this->title->SetPos($this->img->width-$this->img->right_margin,$y,'right',$yalign);
	    elseif( $this->title_adjust=='middle' || $this->title_adjust=='center' ) 
		$this->title->SetPos(($this->img->width-$this->img->left_margin-$this->img->right_margin)/2+$this->img->left_margin,$y,'center',$yalign);
	    elseif($this->title_adjust=='low')
		$this->title->SetPos($this->img->left_margin,$y,'left',$yalign);
	    else {	
		JpGraphError::RaiseL(25060,$this->title_adjust);//('Unknown alignment specified for X-axis title. ('.$this->title_adjust.')');
	    }
	}
	elseif( $this->scale->type == "y" ) {
	    // Add line weight to the height of the axis since
	    // the x-axis could have a width>1 and we want the axis to fit nicely together.
	    if( !$this->hide_line ) 
		$this->img->FilledRectangle($pos-$this->weight+1,$this->img->top_margin,
					    $pos,$this->img->height-$this->img->bottom_margin+$this->weight-1);
	    $x=$pos ;
	    if( $this->title_side == SIDE_LEFT ) {
		$x -= $this->title_margin;
		$x -= $this->title->margin;
		$halign="right";
	    }
	    else {
		$x += $this->title_margin;
		$x += $this->title->margin;
		$halign="left";
	    }
	    // If the user has manually specified an hor. align
	    // then we override the automatic settings with this
	    // specifed setting. Since default is 'left' we compare
	    // with that. (This means a manually set 'left' align
	    // will have no effect.)
	    if( $this->title->halign != 'left' ) 
		$halign = $this->title->halign;
	    if( $this->title_adjust=="high" ) 
		$this->title->SetPos($x,$this->img->top_margin,$halign,"top"); 
	    elseif($this->title_adjust=="middle" || $this->title_adjust=="center")  
		$this->title->SetPos($x,($this->img->height-$this->img->top_margin-$this->img->bottom_margin)/2+$this->img->top_margin,$halign,"center");
	    elseif($this->title_adjust=="low")
		$this->title->SetPos($x,$this->img->height-$this->img->bottom_margin,$halign,"bottom");
	    else	
		JpGraphError::RaiseL(25061,$this->title_adjust);//('Unknown alignment specified for Y-axis title. ('.$this->title_adjust.')');
		
	}
	$this->scale->ticks->Stroke($this->img,$this->scale,$pos);
	if( $aStrokeLabels ) {
	    if( !$this->hide_labels )
		$this->StrokeLabels($pos);
	    $this->title->Stroke($this->img);
	}
    }

//---------------
// PRIVATE METHODS	
    // Draw all the tick labels on major tick marks
    /**
     * StrokeLabels
     * Insert description here
     *
     * @param $aPos
     * @param $aMinor
     * @param $aAbsLabel
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function StrokeLabels($aPos,$aMinor=false,$aAbsLabel=false) {

	$this->img->SetColor($this->label_color);
	$this->img->SetFont($this->font_family,$this->font_style,$this->font_size);
	$yoff=$this->img->GetFontHeight()/2;

	// Only draw labels at major tick marks
	$nbr = count($this->scale->ticks->maj_ticks_label);

	// We have the option to not-display the very first mark
	// (Usefull when the first label might interfere with another
	// axis.)
	$i = $this->show_first_label ? 0 : 1 ;
	if( !$this->show_last_label ) --$nbr;
	// Now run through all labels making sure we don't overshoot the end
	// of the scale.	
	$ncolor=0;
	if( isset($this->ticks_label_colors) )
	    $ncolor=count($this->ticks_label_colors);
	while( $i<$nbr ) {
	    // $tpos holds the absolute text position for the label
	    $tpos=$this->scale->ticks->maj_ticklabels_pos[$i];

	    // Note. the $limit is only used for the x axis since we
	    // might otherwise overshoot if the scale has been centered
	    // This is due to us "loosing" the last tick mark if we center.
	    if( $this->scale->type=="x" && $tpos > $this->img->width-$this->img->right_margin+1 ) {
	    	return; 
	    }
	    // we only draw every $label_step label
	    if( ($i % $this->label_step)==0 ) {

		// Set specific label color if specified
		if( $ncolor > 0 )
		    $this->img->SetColor($this->ticks_label_colors[$i % $ncolor]);
		
		// If the label has been specified use that and in other case
		// just label the mark with the actual scale value 
		$m=$this->scale->ticks->GetMajor();
				
		// ticks_label has an entry for each data point and is the array
		// that holds the labels set by the user. If the user hasn't 
		// specified any values we use whats in the automatically asigned
		// labels in the maj_ticks_label
		if( isset($this->ticks_label[$i*$m]) )
		    $label=$this->ticks_label[$i*$m];
		else {
		    if( $aAbsLabel ) 
			$label=abs($this->scale->ticks->maj_ticks_label[$i]);
		    else
			$label=$this->scale->ticks->maj_ticks_label[$i];
		    if( $this->scale->textscale && $this->scale->ticks->label_formfunc == '' ) {
			++$label;
		    }
		}
					
		if( $this->scale->type == "x" ) {
		    if( $this->labelPos == SIDE_DOWN ) {
			if( $this->label_angle==0 || $this->label_angle==90 ) {
			    if( $this->label_halign=='' && $this->label_valign=='')
				$this->img->SetTextAlign('center','top');
			    else
			    	$this->img->SetTextAlign($this->label_halign,$this->label_valign);
			    
			}
			else {
			    if( $this->label_halign=='' && $this->label_valign=='')
				$this->img->SetTextAlign("right","top");
			    else
				$this->img->SetTextAlign($this->label_halign,$this->label_valign);
			}
			$this->img->StrokeText($tpos,$aPos+$this->tick_label_margin+1,$label,
					       $this->label_angle,$this->label_para_align);
		    }
		    else {
			if( $this->label_angle==0 || $this->label_angle==90 ) {
			    if( $this->label_halign=='' && $this->label_valign=='')
				$this->img->SetTextAlign("center","bottom");
			    else
			    	$this->img->SetTextAlign($this->label_halign,$this->label_valign);
			}
			else {
			    if( $this->label_halign=='' && $this->label_valign=='')
				$this->img->SetTextAlign("right","bottom");
			    else
			    	$this->img->SetTextAlign($this->label_halign,$this->label_valign);
			}
			$this->img->StrokeText($tpos,$aPos-$this->tick_label_margin-1,$label,
					       $this->label_angle,$this->label_para_align);
		    }
		}
		else {
		    // scale->type == "y"
		    //if( $this->label_angle!=0 ) 
		    //JpGraphError::Raise(" Labels at an angle are not supported on Y-axis");
		    if( $this->labelPos == SIDE_LEFT ) { // To the left of y-axis					
			if( $this->label_halign=='' && $this->label_valign=='')	
			    $this->img->SetTextAlign("right","center");
			else
			    $this->img->SetTextAlign($this->label_halign,$this->label_valign);
			$this->img->StrokeText($aPos-$this->tick_label_margin,$tpos,$label,$this->label_angle,$this->label_para_align);	
		    }
		    else { // To the right of the y-axis
			if( $this->label_halign=='' && $this->label_valign=='')	
			    $this->img->SetTextAlign("left","center");
			else
			    $this->img->SetTextAlign($this->label_halign,$this->label_valign);
			$this->img->StrokeText($aPos+$this->tick_label_margin,$tpos,$label,$this->label_angle,$this->label_para_align);	
		    }
		}
	    }
	    ++$i;	
	}								
    }			    

}


//===================================================
// CLASS Ticks
// Description: Abstract base class for drawing linear and logarithmic
// tick marks on axis
//===================================================
/**
 * Ticks
 * Insert description here
 *
 * @category
 * @package
 * @author
 * @copyright
 * @license
 * @version
 * @link
 * @see
 * @since
 */
class Ticks {
    public $label_formatstr='';   // C-style format string to use for labels
    public $label_formfunc='';
    public $label_dateformatstr='';
    public $direction=1; // Should ticks be in(=1) the plot area or outside (=-1)
    public $supress_last=false,$supress_tickmarks=false,$supress_minor_tickmarks=false;
    public $maj_ticks_pos = array(), $maj_ticklabels_pos = array(), 
	   $ticks_pos = array(), $maj_ticks_label = array();
    public $precision;

    protected $minor_abs_size=3, $major_abs_size=5;
    protected $scale;
    protected $is_set=false;
    protected $supress_zerolabel=false,$supress_first=false;
    protected $mincolor="",$majcolor="";
    protected $weight=1;
    protected $label_usedateformat=FALSE;

//---------------
// CONSTRUCTOR
    /**
     * Ticks
     * Insert description here
     *
     * @param $aScale
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function Ticks($aScale) {
	$this->scale=$aScale;
	$this->precision = -1;
    }

//---------------
// PUBLIC METHODS	
    // Set format string for automatic labels
    /**
     * SetLabelFormat
     * Insert description here
     *
     * @param $aFormatString
     * @param $aDate
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetLabelFormat($aFormatString,$aDate=FALSE) {
	$this->label_formatstr=$aFormatString;
	$this->label_usedateformat=$aDate;
    }
	
    /**
     * SetLabelDateFormat
     * Insert description here
     *
     * @param $aFormatString
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetLabelDateFormat($aFormatString) {
	$this->label_dateformatstr=$aFormatString;
    }
	
    /**
     * SetFormatCallback
     * Insert description here
     *
     * @param $aCallbackFuncName
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetFormatCallback($aCallbackFuncName) {
	$this->label_formfunc = $aCallbackFuncName;
    }
	
    // Don't display the first zero label
    /**
     * SupressZeroLabel
     * Insert description here
     *
     * @param $aFlag
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SupressZeroLabel($aFlag=true) {
	$this->supress_zerolabel=$aFlag;
    }
	
    // Don't display minor tick marks
    /**
     * SupressMinorTickMarks
     * Insert description here
     *
     * @param $aHide
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SupressMinorTickMarks($aHide=true) {
	$this->supress_minor_tickmarks=$aHide;
    }
	
    // Don't display major tick marks
    /**
     * SupressTickMarks
     * Insert description here
     *
     * @param $aHide
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SupressTickMarks($aHide=true) {
	$this->supress_tickmarks=$aHide;
    }
	
    // Hide the first tick mark
    /**
     * SupressFirst
     * Insert description here
     *
     * @param $aHide
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SupressFirst($aHide=true) {
	$this->supress_first=$aHide;
    }
	
    // Hide the last tick mark
    /**
     * SupressLast
     * Insert description here
     *
     * @param $aHide
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SupressLast($aHide=true) {
	$this->supress_last=$aHide;
    }

    // Size (in pixels) of minor tick marks
    /**
     * GetMinTickAbsSize
     * Insert description here
     *
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function GetMinTickAbsSize() {
	return $this->minor_abs_size;
    }
	
    // Size (in pixels) of major tick marks
    /**
     * GetMajTickAbsSize
     * Insert description here
     *
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function GetMajTickAbsSize() {
	return $this->major_abs_size;		
    }
	
    /**
     * SetSize
     * Insert description here
     *
     * @param $aMajSize
     * @param $aMinSize
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetSize($aMajSize,$aMinSize=3) {
	$this->major_abs_size = $aMajSize;		
	$this->minor_abs_size = $aMinSize;		
    }

    // Have the ticks been specified
    /**
     * IsSpecified
     * Insert description here
     *
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function IsSpecified() {
	return $this->is_set;
    }
		
    // Specify number of decimals in automatic labels
    // Deprecated from 1.4. Use SetFormatString() instead
    /**
     * SetPrecision
     * Insert description here
     *
     * @param $aPrecision
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetPrecision($aPrecision) { 	
    	if( ERR_DEPRECATED )
	    JpGraphError::RaiseL(25063);//('Ticks::SetPrecision() is deprecated. Use Ticks::SetLabelFormat() (or Ticks::SetFormatCallback()) instead');
	$this->precision=$aPrecision;
    }

    /**
     * SetSide
     * Insert description here
     *
     * @param $aSide
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetSide($aSide) {
	$this->direction=$aSide;
    }
	
    // Which side of the axis should the ticks be on
    /**
     * SetDirection
     * Insert description here
     *
     * @param $aSide
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetDirection($aSide=SIDE_RIGHT) {
	$this->direction=$aSide;
    }
	
    // Set colors for major and minor tick marks
    /**
     * SetMarkColor
     * Insert description here
     *
     * @param $aMajorColor
     * @param $aMinorColor
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetMarkColor($aMajorColor,$aMinorColor="") {
	$this->SetColor($aMajorColor,$aMinorColor);
    }
    
    /**
     * SetColor
     * Insert description here
     *
     * @param $aMajorColor
     * @param $aMinorColor
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetColor($aMajorColor,$aMinorColor="") {
	$this->majcolor=$aMajorColor;
		
	// If not specified use same as major
	if( $aMinorColor=="" ) 
	    $this->mincolor=$aMajorColor;
	else
	    $this->mincolor=$aMinorColor;
    }
	
    /**
     * SetWeight
     * Insert description here
     *
     * @param $aWeight
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetWeight($aWeight) {
	$this->weight=$aWeight;
    }
	
} // Class

//===================================================
// CLASS LinearTicks
// Description: Draw linear ticks on axis
//===================================================
/**
 * LinearTicks
 * Insert description here
 *
 * @category
 * @package
 * @author
 * @copyright
 * @license
 * @version
 * @link
 * @see
 * @since
 */
class LinearTicks extends Ticks {
    public $minor_step=1, $major_step=2;
    public $xlabel_offset=0,$xtick_offset=0;
    private $label_offset=0; // What offset should the displayed label have
    // i.e should we display 0,1,2 or 1,2,3,4 or 2,3,4 etc
    private $text_label_start=0;
    private $iManualTickPos = NULL, $iManualMinTickPos = NULL, $iManualTickLabels = NULL;
    private $iAdjustForDST = false; // If a date falls within the DST period add one hour to the diaplyed time

//---------------
// CONSTRUCTOR
    /**
     * LinearTicks
     * Insert description here
     *
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function LinearTicks() {
	$this->precision = -1;
    }

//---------------
// PUBLIC METHODS	
	
	
    // Return major step size in world coordinates
    /**
     * GetMajor
     * Insert description here
     *
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function GetMajor() {
	return $this->major_step;
    }
	
    // Return minor step size in world coordinates
    /**
     * GetMinor
     * Insert description here
     *
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function GetMinor() {
	return $this->minor_step;
    }
	
    // Set Minor and Major ticks (in world coordinates)
    /**
     * Set
     * Insert description here
     *
     * @param $aMajStep
     * @param $aMinStep
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function Set($aMajStep,$aMinStep=false) {
	if( $aMinStep==false ) 
	    $aMinStep=$aMajStep;
    	
	if( $aMajStep <= 0 || $aMinStep <= 0 ) {
	    JpGraphError::RaiseL(25064);
//(" Minor or major step size is 0. Check that you haven't got an accidental SetTextTicks(0) in your code. If this is not the case you might have stumbled upon a bug in JpGraph. Please report this and if possible include the data that caused the problem.");
	}
		
	$this->major_step=$aMajStep;
	$this->minor_step=$aMinStep;
	$this->is_set = true;
    }

    /**
     * SetMajTickPositions
     * Insert description here
     *
     * @param $aMajPos
     * @param $aLabels
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetMajTickPositions($aMajPos,$aLabels=NULL) {
	$this->SetTickPositions($aMajPos,NULL,$aLabels);
    }

    /**
     * SetTickPositions
     * Insert description here
     *
     * @param $aMajPos
     * @param $aMinPos
     * @param $aLabels
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetTickPositions($aMajPos,$aMinPos=NULL,$aLabels=NULL) {
	if( !is_array($aMajPos) || ($aMinPos!==NULL && !is_array($aMinPos)) ) {
	    JpGraphError::RaiseL(25065);//('Tick positions must be specifued as an array()');
	    return;
	}
	$n=count($aMajPos);
	if( is_array($aLabels) && (count($aLabels) != $n) ) {
	    JpGraphError::RaiseL(25066);//('When manually specifying tick positions and labels the number of labels must be the same as the number of specified ticks.');
	    return;
	}
	$this->iManualTickPos = $aMajPos;
	$this->iManualMinTickPos = $aMinPos;
	$this->iManualTickLabels = $aLabels;
    }

    // Specify all the tick positions manually and possible also the exact labels 
    /**
     * _doManualTickPos
     * Insert description here
     *
     * @param $aScale
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function _doManualTickPos($aScale) { 
	$n=count($this->iManualTickPos);
	$m=count($this->iManualMinTickPos);
	$doLbl=count($this->iManualTickLabels) > 0;

	$this->maj_ticks_pos = array();
	$this->maj_ticklabels_pos = array();
	$this->ticks_pos = array();

	// Now loop through the supplied positions and translate them to screen coordinates
	// and store them in the maj_label_positions
	$minScale = $aScale->scale[0];
	$maxScale = $aScale->scale[1];
	$j=0;
	for($i=0; $i < $n ; ++$i ) {
	    // First make sure that the first tick is not lower than the lower scale value
	    if( !isset($this->iManualTickPos[$i])  || 
		$this->iManualTickPos[$i] < $minScale  || $this->iManualTickPos[$i] > $maxScale) {
		continue;
	    }


	    $this->maj_ticks_pos[$j] = $aScale->Translate($this->iManualTickPos[$i]);
	    $this->maj_ticklabels_pos[$j] = $this->maj_ticks_pos[$j];	

	    // Set the minor tick marks the same as major if not specified
	    if( $m <= 0 ) {
		$this->ticks_pos[$j] = $this->maj_ticks_pos[$j];
	    }

	    if( $doLbl ) { 
		$this->maj_ticks_label[$j] = $this->iManualTickLabels[$i];
	    }
	    else {
		$this->maj_ticks_label[$j]=$this->_doLabelFormat($this->iManualTickPos[$i],$i,$n);
	    }
	    ++$j;
	}

	// Some sanity check
	if( count($this->maj_ticks_pos) < 2 ) {
	    JpGraphError::RaiseL(25067);//('Your manually specified scale and ticks is not correct. The scale seems to be too small to hold any of the specified tickl marks.');
	}

	// Setup the minor tick marks
	$j=0;
	for($i=0; $i < $m; ++$i ) {
	    if(  empty($this->iManualMinTickPos[$i]) || 
		 $this->iManualMinTickPos[$i] < $minScale  || $this->iManualMinTickPos[$i] > $maxScale) 
		continue;
	    $this->ticks_pos[$j] = $aScale->Translate($this->iManualMinTickPos[$i]);
	    ++$j;
	}
    }

    /**
     * _doAutoTickPos
     * Insert description here
     *
     * @param $aScale
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function _doAutoTickPos($aScale) {
	$maj_step_abs = $aScale->scale_factor*$this->major_step;		
	$min_step_abs = $aScale->scale_factor*$this->minor_step;		

	if( $min_step_abs==0 || $maj_step_abs==0 ) {
	    JpGraphError::RaiseL(25068);//("A plot has an illegal scale. This could for example be that you are trying to use text autoscaling to draw a line plot with only one point or that the plot area is too small. It could also be that no input data value is numeric (perhaps only '-' or 'x')");
	}
	// We need to make this an int since comparing it below
	// with the result from round() can give wrong result, such that
	// (40 < 40) == TRUE !!!
	$limit = (int)$aScale->scale_abs[1];	

	if( $aScale->textscale ) {
	    // This can only be true for a X-scale (horizontal)
	    // Define ticks for a text scale. This is slightly different from a 
	    // normal linear type of scale since the position might be adjusted
	    // and the labels start at on
	    $label = (float)$aScale->GetMinVal()+$this->text_label_start+$this->label_offset;	
	    $start_abs=$aScale->scale_factor*$this->text_label_start;
	    $nbrmajticks=round(($aScale->GetMaxVal()-$aScale->GetMinVal()-$this->text_label_start )/$this->major_step)+1;	

	    $x = $aScale->scale_abs[0]+$start_abs+$this->xlabel_offset*$min_step_abs;	
	    for( $i=0; $label <= $aScale->GetMaxVal()+$this->label_offset; ++$i ) {
		// Apply format to label
		$this->maj_ticks_label[$i]=$this->_doLabelFormat($label,$i,$nbrmajticks);
		$label+=$this->major_step;

		// The x-position of the tick marks can be different from the labels.
		// Note that we record the tick position (not the label) so that the grid
		// happen upon tick marks and not labels.
		$xtick=$aScale->scale_abs[0]+$start_abs+$this->xtick_offset*$min_step_abs+$i*$maj_step_abs;
		$this->maj_ticks_pos[$i]=$xtick;
		$this->maj_ticklabels_pos[$i] = round($x);				
		$x += $maj_step_abs;
	    }
	}
	else {
	    $label = $aScale->GetMinVal();	
	    $abs_pos = $aScale->scale_abs[0];
	    $j=0; $i=0;
	    $step = round($maj_step_abs/$min_step_abs);
	    if( $aScale->type == "x" ) {
		// For a normal linear type of scale the major ticks will always be multiples
		// of the minor ticks. In order to avoid any rounding issues the major ticks are
		// defined as every "step" minor ticks and not calculated separately
		$nbrmajticks=round(($aScale->GetMaxVal()-$aScale->GetMinVal()-$this->text_label_start )/$this->major_step)+1; 
		while( round($abs_pos) <= $limit ) {
		    $this->ticks_pos[] = round($abs_pos);
		    $this->ticks_label[] = $label;
		    if( $step== 0 || $i % $step == 0 && $j < $nbrmajticks ) {
			$this->maj_ticks_pos[$j] = round($abs_pos);
			$this->maj_ticklabels_pos[$j] = round($abs_pos);
			$this->maj_ticks_label[$j]=$this->_doLabelFormat($label,$j,$nbrmajticks);
			++$j;
		    }
		    ++$i;
		    $abs_pos += $min_step_abs;
		    $label+=$this->minor_step;
		}
	    }
	    elseif( $aScale->type == "y" ) {
		$nbrmajticks=round(($aScale->GetMaxVal()-$aScale->GetMinVal())/$this->major_step)+1;
		while( round($abs_pos) >= $limit ) {
		    $this->ticks_pos[$i] = round($abs_pos); 
		    $this->ticks_label[$i]=$label;
		    if( $step== 0 || $i % $step == 0 && $j < $nbrmajticks) {
			$this->maj_ticks_pos[$j] = round($abs_pos);
			$this->maj_ticklabels_pos[$j] = round($abs_pos);
			$this->maj_ticks_label[$j]=$this->_doLabelFormat($label,$j,$nbrmajticks);
			++$j;
		    }
		    ++$i;
		    $abs_pos += $min_step_abs;
		    $label += $this->minor_step;
		}	
	    }
	}	
    }

    /**
     * AdjustForDST
     * Insert description here
     *
     * @param $aFlg
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function AdjustForDST($aFlg=true) {
	$this->iAdjustForDST = $aFlg;
    }


    /**
     * _doLabelFormat
     * Insert description here
     *
     * @param $aVal
     * @param $aIdx
     * @param $aNbrTicks
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function _doLabelFormat($aVal,$aIdx,$aNbrTicks) {

	// If precision hasn't been specified set it to a sensible value
	if( $this->precision==-1 ) { 
	    $t = log10($this->minor_step);
	    if( $t > 0 )
		$precision = 0;
	    else
		$precision = -floor($t);
	}
	else
	    $precision = $this->precision;

	if( $this->label_formfunc != '' ) {
	    $f=$this->label_formfunc;
	    $l = call_user_func($f,$aVal);
	}	
	elseif( $this->label_formatstr != '' || $this->label_dateformatstr != '' ) {
	    if( $this->label_usedateformat ) {
		// Adjust the value to take daylight savings into account
		if (date("I",$aVal)==1 && $this->iAdjustForDST ) // DST
		    $aVal+=3600;

		$l = date($this->label_formatstr,$aVal);
		if( $this->label_formatstr == 'W' ) {
		    // If we use week formatting then add a single 'w' in front of the
		    // week number to differentiate it from dates
		    $l = 'w'.$l;
		}
	    }
	    else {
		if( $this->label_dateformatstr !== '' ) {
		    // Adjust the value to take daylight savings into account
		    if (date("I",$aVal)==1 && $this->iAdjustForDST ) // DST
			$aVal+=3600;

		    $l = date($this->label_dateformatstr,$aVal);
		    if( $this->label_formatstr == 'W' ) {
			// If we use week formatting then add a single 'w' in front of the
			// week number to differentiate it from dates
			$l = 'w'.$l;
		    }
		}
		else
		    $l = sprintf($this->label_formatstr,$aVal);
	    }
	}
	else {
	    $l = sprintf('%01.'.$precision.'f',round($aVal,$precision));
	}
	
	if( ($this->supress_zerolabel && $l==0) ||  ($this->supress_first && $aIdx==0) ||
	    ($this->supress_last  && $aIdx==$aNbrTicks-1) ) {
	    $l='';
	}
	return $l;
    }

    // Stroke ticks on either X or Y axis
    /**
     * _StrokeTicks
     * Insert description here
     *
     * @param $aImg
     * @param $aScale
     * @param $aPos
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function _StrokeTicks($aImg,$aScale,$aPos) {
	$hor = $aScale->type == 'x';
	$aImg->SetLineWeight($this->weight);

	// We need to make this an int since comparing it below
	// with the result from round() can give wrong result, such that
	// (40 < 40) == TRUE !!!	
	$limit = (int)$aScale->scale_abs[1];
		
	// A text scale doesn't have any minor ticks
	if( !$aScale->textscale ) {
	    // Stroke minor ticks
	    $yu = $aPos - $this->direction*$this->GetMinTickAbsSize();
	    $xr = $aPos + $this->direction*$this->GetMinTickAbsSize();
	    $n = count($this->ticks_pos);
	    for($i=0; $i < $n; ++$i ) {
		if( !$this->supress_tickmarks && !$this->supress_minor_tickmarks) {
		    if( $this->mincolor!="" ) $aImg->PushColor($this->mincolor);
		    if( $hor ) {
			//if( $this->ticks_pos[$i] <= $limit ) 
			$aImg->Line($this->ticks_pos[$i],$aPos,$this->ticks_pos[$i],$yu); 
		    }
		    else {
			//if( $this->ticks_pos[$i] >= $limit ) 
			$aImg->Line($aPos,$this->ticks_pos[$i],$xr,$this->ticks_pos[$i]); 
		    }
		    if( $this->mincolor!="" ) $aImg->PopColor();
		}
	    }
	}

	// Stroke major ticks
	$yu = $aPos - $this->direction*$this->GetMajTickAbsSize();
	$xr = $aPos + $this->direction*$this->GetMajTickAbsSize();
	$nbrmajticks=round(($aScale->GetMaxVal()-$aScale->GetMinVal()-$this->text_label_start )/$this->major_step)+1;
	$n = count($this->maj_ticks_pos);
	for($i=0; $i < $n ; ++$i ) {
	    if(!($this->xtick_offset > 0 && $i==$nbrmajticks-1) && !$this->supress_tickmarks) {
		if( $this->majcolor!="" ) $aImg->PushColor($this->majcolor);
		if( $hor ) {
		    //if( $this->maj_ticks_pos[$i] <= $limit ) 
		    $aImg->Line($this->maj_ticks_pos[$i],$aPos,$this->maj_ticks_pos[$i],$yu); 
		}
		else {
		    //if( $this->maj_ticks_pos[$i] >= $limit ) 
		    $aImg->Line($aPos,$this->maj_ticks_pos[$i],$xr,$this->maj_ticks_pos[$i]); 
		}
		if( $this->majcolor!="" ) $aImg->PopColor();
	    }
	}
	
    }

    // Draw linear ticks
    /**
     * Stroke
     * Insert description here
     *
     * @param $aImg
     * @param $aScale
     * @param $aPos
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function Stroke($aImg,$aScale,$aPos) {
	if( $this->iManualTickPos != NULL ) 
	    $this->_doManualTickPos($aScale);
	else 
	    $this->_doAutoTickPos($aScale);
	$this->_StrokeTicks($aImg,$aScale,$aPos, $aScale->type == 'x' );
    }

//---------------
// PRIVATE METHODS
    // Spoecify the offset of the displayed tick mark with the tick "space"
    // Legal values for $o is [0,1] used to adjust where the tick marks and label 
    // should be positioned within the major tick-size
    // $lo specifies the label offset and $to specifies the tick offset
    // this comes in handy for example in bar graphs where we wont no offset for the
    // tick but have the labels displayed halfway under the bars.
    /**
     * SetXLabelOffset
     * Insert description here
     *
     * @param $aLabelOff
     * @param $aTickOff
     * @param 1
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetXLabelOffset($aLabelOff,$aTickOff=-1) {
	$this->xlabel_offset=$aLabelOff;
	if( $aTickOff==-1 )	// Same as label offset
	    $this->xtick_offset=$aLabelOff;
	else
	    $this->xtick_offset=$aTickOff;
	if( $aLabelOff>0 )
	    $this->SupressLast();	// The last tick wont fit
    }

    // Which tick label should we start with?
    /**
     * SetTextLabelStart
     * Insert description here
     *
     * @param $aTextLabelOff
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetTextLabelStart($aTextLabelOff) {
	$this->text_label_start=$aTextLabelOff;
    }
	
} // Class

//===================================================
// CLASS LinearScale
// Description: Handle linear scaling between screen and world 
//===================================================
/**
 * LinearScale
 * Insert description here
 *
 * @category
 * @package
 * @author
 * @copyright
 * @license
 * @version
 * @link
 * @see
 * @since
 */
class LinearScale {
    public $textscale=false; // Just a flag to let the Plot class find out if
    // we are a textscale or not. This is a cludge since
    // this information is available in Graph::axtype but
    // we don't have access to the graph object in the Plots
    // stroke method. So we let graph store the status here
    // when the linear scale is created. A real cludge...
    public $type; // is this x or y scale ?
    public $ticks=null; // Store ticks
    public $text_scale_off = 0;
    public $scale_abs=array(0,0);
    public $scale_factor; // Scale factor between world and screen
    public $off; // Offset between image edge and plot area
    public $scale=array(0,0);
    public $name = 'lin';
    public $auto_ticks=false; // When using manual scale should the ticks be automatically set?
    public $world_abs_size; // Plot area size in pixels (Needed public in jpgraph_radar.php)
    public $world_size;	// Plot area size in world coordinates
    public $intscale=false; // Restrict autoscale to integers
    protected $autoscale_min=false; // Forced minimum value, auto determine max
    protected $autoscale_max=false; // Forced maximum value, auto determine min
    private $gracetop=0,$gracebottom=0;
//---------------
// CONSTRUCTOR
    /**
     * LinearScale
     * Insert description here
     *
     * @param $aMin
     * @param $aMax
     * @param $aType
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function LinearScale($aMin=0,$aMax=0,$aType="y") {
	assert($aType=="x" || $aType=="y" );
	assert($aMin<=$aMax);
		
	$this->type=$aType;
	$this->scale=array($aMin,$aMax);		
	$this->world_size=$aMax-$aMin;	
	$this->ticks = new LinearTicks();
    }

//---------------
// PUBLIC METHODS	
    // Check if scale is set or if we should autoscale
    // We should do this is either scale or ticks has not been set
    /**
     * IsSpecified
     * Insert description here
     *
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function IsSpecified() {
	if( $this->GetMinVal()==$this->GetMaxVal() ) {		// Scale not set
	    return false;
	}
	return true;
    }
	
    // Set the minimum data value when the autoscaling is used. 
    // Usefull if you want a fix minimum (like 0) but have an
    // automatic maximum
    /**
     * SetAutoMin
     * Insert description here
     *
     * @param $aMin
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetAutoMin($aMin) {
	$this->autoscale_min=$aMin;
    }

    // Set the minimum data value when the autoscaling is used. 
    // Usefull if you want a fix minimum (like 0) but have an
    // automatic maximum
    /**
     * SetAutoMax
     * Insert description here
     *
     * @param $aMax
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetAutoMax($aMax) {
	$this->autoscale_max=$aMax;
    }

    // If the user manually specifies a scale should the ticks
    // still be set automatically?
    /**
     * SetAutoTicks
     * Insert description here
     *
     * @param $aFlag
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetAutoTicks($aFlag=true) {
	$this->auto_ticks = $aFlag;
    }

    // Specify scale "grace" value (top and bottom)
    /**
     * SetGrace
     * Insert description here
     *
     * @param $aGraceTop
     * @param $aGraceBottom
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetGrace($aGraceTop,$aGraceBottom=0) {
	if( $aGraceTop<0 || $aGraceBottom < 0  )
	    JpGraphError::RaiseL(25069);//(" Grace must be larger then 0");
	$this->gracetop=$aGraceTop;
	$this->gracebottom=$aGraceBottom;
    }
	
    // Get the minimum value in the scale
    /**
     * GetMinVal
     * Insert description here
     *
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function GetMinVal() {
	return $this->scale[0];
    }
	
    // get maximum value for scale
    /**
     * GetMaxVal
     * Insert description here
     *
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function GetMaxVal() {
	return $this->scale[1];
    }
		
    // Specify a new min/max value for sclae	
    /**
     * Update
     * Insert description here
     *
     * @param $aImg
     * @param $aMin
     * @param $aMax
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function Update($aImg,$aMin,$aMax) {
	$this->scale=array($aMin,$aMax);		
	$this->world_size=$aMax-$aMin;		
	$this->InitConstants($aImg);					
    }
	
    // Translate between world and screen
    /**
     * Translate
     * Insert description here
     *
     * @param $aCoord
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function Translate($aCoord) {
	if( !is_numeric($aCoord) ) {
	    if( $aCoord != '' && $aCoord != '-' && $aCoord != 'x' ) 
		JpGraphError::RaiseL(25070);//('Your data contains non-numeric values.');
	    return 0;
	}
	else {
	    return $this->off+($aCoord - $this->scale[0]) * $this->scale_factor; 
	}
    }
	
    // Relative translate (don't include offset) usefull when we just want
    // to know the relative position (in pixels) on the axis
    /**
     * RelTranslate
     * Insert description here
     *
     * @param $aCoord
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function RelTranslate($aCoord) {
	if( !is_numeric($aCoord) ) {
	    if( $aCoord != '' && $aCoord != '-' && $aCoord != 'x'  ) 
		JpGraphError::RaiseL(25070);//('Your data contains non-numeric values.');
	    return 0;
	}
	else { 
	    return ($aCoord - $this->scale[0]) * $this->scale_factor; 
	}
    }
	
    // Restrict autoscaling to only use integers
    /**
     * SetIntScale
     * Insert description here
     *
     * @param $aIntScale
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetIntScale($aIntScale=true) {
	$this->intscale=$aIntScale;
    }
	
    // Calculate an integer autoscale
    /**
     * IntAutoScale
     * Insert description here
     *
     * @param $img
     * @param $min
     * @param $max
     * @param $maxsteps
     * @param $majend
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function IntAutoScale($img,$min,$max,$maxsteps,$majend=true) {
	// Make sure limits are integers
	$min=floor($min);
	$max=ceil($max);
	if( abs($min-$max)==0 ) {
	    --$min; ++$max;
	}
	$maxsteps = floor($maxsteps);
		
	$gracetop=round(($this->gracetop/100.0)*abs($max-$min));
	$gracebottom=round(($this->gracebottom/100.0)*abs($max-$min));
	if( is_numeric($this->autoscale_min) ) {
	    $min = ceil($this->autoscale_min);
	    if( $min >= $max ) {
		JpGraphError::RaiseL(25071);//('You have specified a min value with SetAutoMin() which is larger than the maximum value used for the scale. This is not possible.');
	    }
	}

	if( is_numeric($this->autoscale_max) ) {
	    $max = ceil($this->autoscale_max);
	    if( $min >= $max ) {
		JpGraphError::RaiseL(25072);//('You have specified a max value with SetAutoMax() which is smaller than the miminum value used for the scale. This is not possible.');
	    }
	}

	if( abs($min-$max ) == 0 ) {
	    ++$max;
	    --$min;
	}
			
	$min -= $gracebottom;
	$max += $gracetop;		

	// First get tickmarks as multiples of 1, 10, ...	
	if( $majend ) {
	    list($num1steps,$adj1min,$adj1max,$maj1step) = 
		$this->IntCalcTicks($maxsteps,$min,$max,1);
	}
	else {
	    $adj1min = $min;
	    $adj1max = $max;
	    list($num1steps,$maj1step) = 
		$this->IntCalcTicksFreeze($maxsteps,$min,$max,1);
	}

	if( abs($min-$max) > 2 ) {
	    // Then get tick marks as 2:s 2, 20, ...
	    if( $majend ) {
		list($num2steps,$adj2min,$adj2max,$maj2step) = 
		    $this->IntCalcTicks($maxsteps,$min,$max,5);
	    }
	    else {
		$adj2min = $min;
		$adj2max = $max;
		list($num2steps,$maj2step) = 
		    $this->IntCalcTicksFreeze($maxsteps,$min,$max,5);
	    }
	}
	else {
	    $num2steps = 10000;	// Dummy high value so we don't choose this
	}
	
	if( abs($min-$max) > 5 ) {	
	    // Then get tickmarks as 5:s 5, 50, 500, ...
	    if( $majend ) {
		list($num5steps,$adj5min,$adj5max,$maj5step) = 
		    $this->IntCalcTicks($maxsteps,$min,$max,2);
	    }
	    else {
		$adj5min = $min;
		$adj5max = $max;
		list($num5steps,$maj5step) = 
		    $this->IntCalcTicksFreeze($maxsteps,$min,$max,2);
	    }
	}
	else {
	    $num5steps = 10000;	// Dummy high value so we don't choose this		
	}
	
	// Check to see whichof 1:s, 2:s or 5:s fit better with
	// the requested number of major ticks		
	$match1=abs($num1steps-$maxsteps);		
	$match2=abs($num2steps-$maxsteps);
	if( !empty($maj5step) && $maj5step > 1 )
	    $match5=abs($num5steps-$maxsteps);
	else
	    $match5=10000; 	// Dummy high value 
		
	// Compare these three values and see which is the closest match
	// We use a 0.6 weight to gravitate towards multiple of 5:s 
	if( $match1 < $match2 ) {
	    if( $match1 < $match5 )
		$r=1;			
	    else 
		$r=3;
	}
	else {
	    if( $match2 < $match5 )
		$r=2;			
	    else 
		$r=3;		
	}	
	// Minsteps are always the same as maxsteps for integer scale
	switch( $r ) {
	    case 1:
		$this->ticks->Set($maj1step,$maj1step);
		$this->Update($img,$adj1min,$adj1max);
		break;			
	    case 2:
		$this->ticks->Set($maj2step,$maj2step);
		$this->Update($img,$adj2min,$adj2max);		
		break;									
	    case 3:
		$this->ticks->Set($maj5step,$maj5step);		
		$this->Update($img,$adj5min,$adj5max);
		break;			
	    default:
		JpGraphError::RaiseL(25073,$r);//('Internal error. Integer scale algorithm comparison out of bound (r=$r)');
	}		
    }
	
	
    // Calculate autoscale. Used if user hasn't given a scale and ticks
    // $maxsteps is the maximum number of major tickmarks allowed.
    /**
     * AutoScale
     * Insert description here
     *
     * @param $img
     * @param $min
     * @param $max
     * @param $maxsteps
     * @param $majend
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function AutoScale($img,$min,$max,$maxsteps,$majend=true) {
	if( $this->intscale ) {	
	    $this->IntAutoScale($img,$min,$max,$maxsteps,$majend);
	    return;
	}
	if( abs($min-$max) < 0.00001 ) {
	    // We need some difference to be able to autoscale
	    // make it 5% above and 5% below value
	    if( $min==0 && $max==0 ) {		// Special case
		$min=-1; $max=1;
	    }
	    else {
		$delta = (abs($max)+abs($min))*0.005;
		$min -= $delta;
		$max += $delta;
	    }
	}
		
	$gracetop=($this->gracetop/100.0)*abs($max-$min);
	$gracebottom=($this->gracebottom/100.0)*abs($max-$min);
	if( is_numeric($this->autoscale_min) ) {
	    $min = $this->autoscale_min;
	    if( $min >= $max ) {
		JpGraphError::RaiseL(25071);//('You have specified a min value with SetAutoMin() which is larger than the maximum value used for the scale. This is not possible.');
	    }
	    if( abs($min-$max ) < 0.00001 )
		$max *= 1.2;
	}

	if( is_numeric($this->autoscale_max) ) {
	    $max = $this->autoscale_max;
	    if( $min >= $max ) {
		JpGraphError::RaiseL(25072);//('You have specified a max value with SetAutoMax() which is smaller than the miminum value used for the scale. This is not possible.');
	    }
	    if( abs($min-$max ) < 0.00001 )
		$min *= 0.8;
	}

	$min -= $gracebottom;
	$max += $gracetop;


	// First get tickmarks as multiples of 0.1, 1, 10, ...	
	if( $majend ) {
	    list($num1steps,$adj1min,$adj1max,$min1step,$maj1step) = 
		$this->CalcTicks($maxsteps,$min,$max,1,2);
	}
	else {
	    $adj1min=$min;
	    $adj1max=$max;
	    list($num1steps,$min1step,$maj1step) = 
		$this->CalcTicksFreeze($maxsteps,$min,$max,1,2,false);
	}
		
	// Then get tick marks as 2:s 0.2, 2, 20, ...
	if( $majend ) {
	    list($num2steps,$adj2min,$adj2max,$min2step,$maj2step) = 
		$this->CalcTicks($maxsteps,$min,$max,5,2);
	}
	else {
	    $adj2min=$min;
	    $adj2max=$max;
	    list($num2steps,$min2step,$maj2step) = 
		$this->CalcTicksFreeze($maxsteps,$min,$max,5,2,false);
	}
		
	// Then get tickmarks as 5:s 0.05, 0.5, 5, 50, ...
	if( $majend ) {
	    list($num5steps,$adj5min,$adj5max,$min5step,$maj5step) = 
		$this->CalcTicks($maxsteps,$min,$max,2,5);		
	}
	else {
	    $adj5min=$min;
	    $adj5max=$max;
	    list($num5steps,$min5step,$maj5step) = 
		$this->CalcTicksFreeze($maxsteps,$min,$max,2,5,false);
	}

	// Check to see whichof 1:s, 2:s or 5:s fit better with
	// the requested number of major ticks		
	$match1=abs($num1steps-$maxsteps);		
	$match2=abs($num2steps-$maxsteps);
	$match5=abs($num5steps-$maxsteps);
	// Compare these three values and see which is the closest match
	// We use a 0.8 weight to gravitate towards multiple of 5:s 
	$r=$this->MatchMin3($match1,$match2,$match5,0.8);
	switch( $r ) {
	    case 1:
		$this->Update($img,$adj1min,$adj1max);
		$this->ticks->Set($maj1step,$min1step);
		break;			
	    case 2:
		$this->Update($img,$adj2min,$adj2max);		
		$this->ticks->Set($maj2step,$min2step);
		break;									
	    case 3:
		$this->Update($img,$adj5min,$adj5max);
		$this->ticks->Set($maj5step,$min5step);		
		break;			
	}
    }

//---------------
// PRIVATE METHODS	

    // This method recalculates all constants that are depending on the
    // margins in the image. If the margins in the image are changed
    // this method should be called for every scale that is registred with
    // that image. Should really be installed as an observer of that image.
    /**
     * InitConstants
     * Insert description here
     *
     * @param $img
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function InitConstants($img) {
	if( $this->type=="x" ) {
	    $this->world_abs_size=$img->width - $img->left_margin - $img->right_margin;
	    $this->off=$img->left_margin;
	    $this->scale_factor = 0;
	    if( $this->world_size > 0 )
		$this->scale_factor=$this->world_abs_size/($this->world_size*1.0);
	}
	else { // y scale
	    $this->world_abs_size=$img->height - $img->top_margin - $img->bottom_margin; 
	    $this->off=$img->top_margin+$this->world_abs_size;			
	    $this->scale_factor = 0;			
	    if( $this->world_size > 0 )			
		$this->scale_factor=-$this->world_abs_size/($this->world_size*1.0);	
	}
	$size = $this->world_size * $this->scale_factor;
	$this->scale_abs=array($this->off,$this->off + $size);	
    }
	
    // Initialize the conversion constants for this scale
    // This tries to pre-calculate as much as possible to speed up the
    // actual conversion (with Translate()) later on
    // $start	=scale start in absolute pixels (for x-scale this is an y-position
    //				 and for an y-scale this is an x-position
    // $len 		=absolute length in pixels of scale 			
    /**
     * SetConstants
     * Insert description here
     *
     * @param $aStart
     * @param $aLen
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetConstants($aStart,$aLen) {
	$this->world_abs_size=$aLen;
	$this->off=$aStart;
		
	if( $this->world_size<=0 ) {
	    // This should never ever happen !!
	    JpGraphError::RaiseL(25074);
//("You have unfortunately stumbled upon a bug in JpGraph. It seems like the scale range is ".$this->world_size." [for ".$this->type." scale] <br> Please report Bug #01 to jpgraph@aditus.nu and include the script that gave this error. This problem could potentially be caused by trying to use \"illegal\" values in the input data arrays (like trying to send in strings or only NULL values) which causes the autoscaling to fail.");

	}
		
	// scale_factor = number of pixels per world unit
	$this->scale_factor=$this->world_abs_size/($this->world_size*1.0);
		
	// scale_abs = start and end points of scale in absolute pixels
	$this->scale_abs=array($this->off,$this->off+$this->world_size*$this->scale_factor);		
    }
	
	
    // Calculate number of ticks steps with a specific division
    // $a is the divisor of 10**x to generate the first maj tick intervall
    // $a=1, $b=2 give major ticks with multiple of 10, ...,0.1,1,10,...
    // $a=5, $b=2 give major ticks with multiple of 2:s ...,0.2,2,20,...
    // $a=2, $b=5 give major ticks with multiple of 5:s ...,0.5,5,50,...
    // We return a vector of
    // 	[$numsteps,$adjmin,$adjmax,$minstep,$majstep]
    // If $majend==true then the first and last marks on the axis will be major
    // labeled tick marks otherwise it will be adjusted to the closest min tick mark
    /**
     * CalcTicks
     * Insert description here
     *
     * @param $maxsteps
     * @param $min
     * @param $max
     * @param $a
     * @param $b
     * @param $majend
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function CalcTicks($maxsteps,$min,$max,$a,$b,$majend=true) {
	$diff=$max-$min; 
	if( $diff==0 )
	    $ld=0;
	else
	    $ld=floor(log10($diff));

	// Gravitate min towards zero if we are close		
	if( $min>0 && $min < pow(10,$ld) ) $min=0;
		
	//$majstep=pow(10,$ld-1)/$a; 
	$majstep=pow(10,$ld)/$a; 
	$minstep=$majstep/$b;
	
	$adjmax=ceil($max/$minstep)*$minstep;
	$adjmin=floor($min/$minstep)*$minstep;	
	$adjdiff = $adjmax-$adjmin;
	$numsteps=$adjdiff/$majstep; 
	
	while( $numsteps>$maxsteps ) {
	    $majstep=pow(10,$ld)/$a; 
	    $numsteps=$adjdiff/$majstep;
	    ++$ld;
	}

	$minstep=$majstep/$b;
	$adjmin=floor($min/$minstep)*$minstep;	
	$adjdiff = $adjmax-$adjmin;		
	if( $majend ) {
	    $adjmin = floor($min/$majstep)*$majstep;	
	    $adjdiff = $adjmax-$adjmin;		
	    $adjmax = ceil($adjdiff/$majstep)*$majstep+$adjmin;
	}
	else
	    $adjmax=ceil($max/$minstep)*$minstep;

	return array($numsteps,$adjmin,$adjmax,$minstep,$majstep);
    }

    /**
     * CalcTicksFreeze
     * Insert description here
     *
     * @param $maxsteps
     * @param $min
     * @param $max
     * @param $a
     * @param $b
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function CalcTicksFreeze($maxsteps,$min,$max,$a,$b) {
	// Same as CalcTicks but don't adjust min/max values
	$diff=$max-$min; 
	if( $diff==0 )
	    $ld=0;
	else
	    $ld=floor(log10($diff));

	//$majstep=pow(10,$ld-1)/$a; 
	$majstep=pow(10,$ld)/$a; 
	$minstep=$majstep/$b;
	$numsteps=floor($diff/$majstep); 
	
	while( $numsteps > $maxsteps ) {
	    $majstep=pow(10,$ld)/$a; 
	    $numsteps=floor($diff/$majstep);
	    ++$ld;
	}
	$minstep=$majstep/$b;
	return array($numsteps,$minstep,$majstep);
    }

	
    /**
     * IntCalcTicks
     * Insert description here
     *
     * @param $maxsteps
     * @param $min
     * @param $max
     * @param $a
     * @param $majend
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function IntCalcTicks($maxsteps,$min,$max,$a,$majend=true) {
	$diff=$max-$min; 
	if( $diff==0 )
	    JpGraphError::RaiseL(25075);//('Can\'t automatically determine ticks since min==max.');
	else
	    $ld=floor(log10($diff));
		
	// Gravitate min towards zero if we are close		
	if( $min>0 && $min < pow(10,$ld) ) $min=0;
		
	if( $ld == 0 ) $ld=1;
	
	if( $a == 1 ) 
	    $majstep = 1;
	else
	    $majstep=pow(10,$ld)/$a; 
	$adjmax=ceil($max/$majstep)*$majstep;

	$adjmin=floor($min/$majstep)*$majstep;	
	$adjdiff = $adjmax-$adjmin;
	$numsteps=$adjdiff/$majstep; 
	while( $numsteps>$maxsteps ) {
	    $majstep=pow(10,$ld)/$a; 
	    $numsteps=$adjdiff/$majstep;
	    ++$ld;
	}
		
	$adjmin=floor($min/$majstep)*$majstep;	
	$adjdiff = $adjmax-$adjmin;		
	if( $majend ) {
	    $adjmin = floor($min/$majstep)*$majstep;	
	    $adjdiff = $adjmax-$adjmin;		
	    $adjmax = ceil($adjdiff/$majstep)*$majstep+$adjmin;
	}
	else
	    $adjmax=ceil($max/$majstep)*$majstep;
			
	return array($numsteps,$adjmin,$adjmax,$majstep);		
    }


    /**
     * IntCalcTicksFreeze
     * Insert description here
     *
     * @param $maxsteps
     * @param $min
     * @param $max
     * @param $a
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function IntCalcTicksFreeze($maxsteps,$min,$max,$a) {
	// Same as IntCalcTick but don't change min/max values
	$diff=$max-$min; 
	if( $diff==0 )
	    JpGraphError::RaiseL(25075);//('Can\'t automatically determine ticks since min==max.');
	else
	    $ld=floor(log10($diff));
		
	if( $ld == 0 ) $ld=1;
	
	if( $a == 1 ) 
	    $majstep = 1;
	else
	    $majstep=pow(10,$ld)/$a; 

	$numsteps=floor($diff/$majstep); 
	while( $numsteps > $maxsteps ) {
	    $majstep=pow(10,$ld)/$a; 
	    $numsteps=floor($diff/$majstep);
	    ++$ld;
	}
					
	return array($numsteps,$majstep);		
    }


	
    // Determine the minimum of three values witha  weight for last value
    /**
     * MatchMin3
     * Insert description here
     *
     * @param $a
     * @param $b
     * @param $c
     * @param $weight
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function MatchMin3($a,$b,$c,$weight) {
	if( $a < $b ) {
	    if( $a < ($c*$weight) ) 
		return 1; // $a smallest
	    else 
		return 3; // $c smallest
	}
	elseif( $b < ($c*$weight) ) 
	    return 2; // $b smallest
	return 3; // $c smallest
    }
} // Class

//===================================================
// CLASS RGB
// Description: Color definitions as RGB triples
//===================================================
/**
 * RGB
 * Insert description here
 *
 * @category
 * @package
 * @author
 * @copyright
 * @license
 * @version
 * @link
 * @see
 * @since
 */
class RGB {
    public $rgb_table; 
    public $img;

    /**
     * RGB
     * Insert description here
     *
     * @param $aImg
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function RGB($aImg=null) {
	$this->img = $aImg;
		
	// Conversion array between color names and RGB
	$this->rgb_table = array(
	    "aqua"=> array(0,255,255),		
	    "lime"=> array(0,255,0),		
	    "teal"=> array(0,128,128),
	    "whitesmoke"=>array(245,245,245),
	    "gainsboro"=>array(220,220,220),
	    "oldlace"=>array(253,245,230),
	    "linen"=>array(250,240,230),
	    "antiquewhite"=>array(250,235,215),
	    "papayawhip"=>array(255,239,213),
	    "blanchedalmond"=>array(255,235,205),
	    "bisque"=>array(255,228,196),
	    "peachpuff"=>array(255,218,185),
	    "navajowhite"=>array(255,222,173),
	    "moccasin"=>array(255,228,181),
	    "cornsilk"=>array(255,248,220),
	    "ivory"=>array(255,255,240),
	    "lemonchiffon"=>array(255,250,205),
	    "seashell"=>array(255,245,238),
	    "mintcream"=>array(245,255,250),
	    "azure"=>array(240,255,255),
	    "aliceblue"=>array(240,248,255),
	    "lavender"=>array(230,230,250),
	    "lavenderblush"=>array(255,240,245),
	    "mistyrose"=>array(255,228,225),
	    "white"=>array(255,255,255),
	    "black"=>array(0,0,0),
	    "darkslategray"=>array(47,79,79),
	    "dimgray"=>array(105,105,105),
	    "slategray"=>array(112,128,144),
	    "lightslategray"=>array(119,136,153),
	    "gray"=>array(190,190,190),
	    "lightgray"=>array(211,211,211),
	    "midnightblue"=>array(25,25,112),
	    "navy"=>array(0,0,128),
	    "cornflowerblue"=>array(100,149,237),
	    "darkslateblue"=>array(72,61,139),
	    "slateblue"=>array(106,90,205),
	    "mediumslateblue"=>array(123,104,238),
	    "lightslateblue"=>array(132,112,255),
	    "mediumblue"=>array(0,0,205),
	    "royalblue"=>array(65,105,225),
	    "blue"=>array(0,0,255),
	    "dodgerblue"=>array(30,144,255),
	    "deepskyblue"=>array(0,191,255),
	    "skyblue"=>array(135,206,235),
	    "lightskyblue"=>array(135,206,250),
	    "steelblue"=>array(70,130,180),
	    "lightred"=>array(211,167,168),
	    "lightsteelblue"=>array(176,196,222),
	    "lightblue"=>array(173,216,230),
	    "powderblue"=>array(176,224,230),
	    "paleturquoise"=>array(175,238,238),
	    "darkturquoise"=>array(0,206,209),
	    "mediumturquoise"=>array(72,209,204),
	    "turquoise"=>array(64,224,208),
	    "cyan"=>array(0,255,255),
	    "lightcyan"=>array(224,255,255),
	    "cadetblue"=>array(95,158,160),
	    "mediumaquamarine"=>array(102,205,170),
	    "aquamarine"=>array(127,255,212),
	    "darkgreen"=>array(0,100,0),
	    "darkolivegreen"=>array(85,107,47),
	    "darkseagreen"=>array(143,188,143),
	    "seagreen"=>array(46,139,87),
	    "mediumseagreen"=>array(60,179,113),
	    "lightseagreen"=>array(32,178,170),
	    "palegreen"=>array(152,251,152),
	    "springgreen"=>array(0,255,127),
	    "lawngreen"=>array(124,252,0),
	    "green"=>array(0,255,0),
	    "chartreuse"=>array(127,255,0),
	    "mediumspringgreen"=>array(0,250,154),
	    "greenyellow"=>array(173,255,47),
	    "limegreen"=>array(50,205,50),
	    "yellowgreen"=>array(154,205,50),
	    "forestgreen"=>array(34,139,34),
	    "olivedrab"=>array(107,142,35),
	    "darkkhaki"=>array(189,183,107),
	    "khaki"=>array(240,230,140),
	    "palegoldenrod"=>array(238,232,170),
	    "lightgoldenrodyellow"=>array(250,250,210),
	    "lightyellow"=>array(255,255,200),
	    "yellow"=>array(255,255,0),
	    "gold"=>array(255,215,0),
	    "lightgoldenrod"=>array(238,221,130),
	    "goldenrod"=>array(218,165,32),
	    "darkgoldenrod"=>array(184,134,11),
	    "rosybrown"=>array(188,143,143),
	    "indianred"=>array(205,92,92),
	    "saddlebrown"=>array(139,69,19),
	    "sienna"=>array(160,82,45),
	    "peru"=>array(205,133,63),
	    "burlywood"=>array(222,184,135),
	    "beige"=>array(245,245,220),
	    "wheat"=>array(245,222,179),
	    "sandybrown"=>array(244,164,96),
	    "tan"=>array(210,180,140),
	    "chocolate"=>array(210,105,30),
	    "firebrick"=>array(178,34,34),
	    "brown"=>array(165,42,42),
	    "darksalmon"=>array(233,150,122),
	    "salmon"=>array(250,128,114),
	    "lightsalmon"=>array(255,160,122),
	    "orange"=>array(255,165,0),
	    "darkorange"=>array(255,140,0),
	    "coral"=>array(255,127,80),
	    "lightcoral"=>array(240,128,128),
	    "tomato"=>array(255,99,71),
	    "orangered"=>array(255,69,0),
	    "red"=>array(255,0,0),
	    "hotpink"=>array(255,105,180),
	    "deeppink"=>array(255,20,147),
	    "pink"=>array(255,192,203),
	    "lightpink"=>array(255,182,193),
	    "palevioletred"=>array(219,112,147),
	    "maroon"=>array(176,48,96),
	    "mediumvioletred"=>array(199,21,133),
	    "violetred"=>array(208,32,144),
	    "magenta"=>array(255,0,255),
	    "violet"=>array(238,130,238),
	    "plum"=>array(221,160,221),
	    "orchid"=>array(218,112,214),
	    "mediumorchid"=>array(186,85,211),
	    "darkorchid"=>array(153,50,204),
	    "darkviolet"=>array(148,0,211),
	    "blueviolet"=>array(138,43,226),
	    "purple"=>array(160,32,240),
	    "mediumpurple"=>array(147,112,219),
	    "thistle"=>array(216,191,216),
	    "snow1"=>array(255,250,250),
	    "snow2"=>array(238,233,233),
	    "snow3"=>array(205,201,201),
	    "snow4"=>array(139,137,137),
	    "seashell1"=>array(255,245,238),
	    "seashell2"=>array(238,229,222),
	    "seashell3"=>array(205,197,191),
	    "seashell4"=>array(139,134,130),
	    "AntiqueWhite1"=>array(255,239,219),
	    "AntiqueWhite2"=>array(238,223,204),
	    "AntiqueWhite3"=>array(205,192,176),
	    "AntiqueWhite4"=>array(139,131,120),
	    "bisque1"=>array(255,228,196),
	    "bisque2"=>array(238,213,183),
	    "bisque3"=>array(205,183,158),
	    "bisque4"=>array(139,125,107),
	    "peachPuff1"=>array(255,218,185),
	    "peachpuff2"=>array(238,203,173),
	    "peachpuff3"=>array(205,175,149),
	    "peachpuff4"=>array(139,119,101),
	    "navajowhite1"=>array(255,222,173),
	    "navajowhite2"=>array(238,207,161),
	    "navajowhite3"=>array(205,179,139),
	    "navajowhite4"=>array(139,121,94),
	    "lemonchiffon1"=>array(255,250,205),
	    "lemonchiffon2"=>array(238,233,191),
	    "lemonchiffon3"=>array(205,201,165),
	    "lemonchiffon4"=>array(139,137,112),
	    "ivory1"=>array(255,255,240),
	    "ivory2"=>array(238,238,224),
	    "ivory3"=>array(205,205,193),
	    "ivory4"=>array(139,139,131),
	    "honeydew"=>array(193,205,193),
	    "lavenderblush1"=>array(255,240,245),
	    "lavenderblush2"=>array(238,224,229),
	    "lavenderblush3"=>array(205,193,197),
	    "lavenderblush4"=>array(139,131,134),
	    "mistyrose1"=>array(255,228,225),
	    "mistyrose2"=>array(238,213,210),
	    "mistyrose3"=>array(205,183,181),
	    "mistyrose4"=>array(139,125,123),
	    "azure1"=>array(240,255,255),
	    "azure2"=>array(224,238,238),
	    "azure3"=>array(193,205,205),
	    "azure4"=>array(131,139,139),
	    "slateblue1"=>array(131,111,255),
	    "slateblue2"=>array(122,103,238),
	    "slateblue3"=>array(105,89,205),
	    "slateblue4"=>array(71,60,139),
	    "royalblue1"=>array(72,118,255),
	    "royalblue2"=>array(67,110,238),
	    "royalblue3"=>array(58,95,205),
	    "royalblue4"=>array(39,64,139),
	    "dodgerblue1"=>array(30,144,255),
	    "dodgerblue2"=>array(28,134,238),
	    "dodgerblue3"=>array(24,116,205),
	    "dodgerblue4"=>array(16,78,139),
	    "steelblue1"=>array(99,184,255),
	    "steelblue2"=>array(92,172,238),
	    "steelblue3"=>array(79,148,205),
	    "steelblue4"=>array(54,100,139),
	    "deepskyblue1"=>array(0,191,255),
	    "deepskyblue2"=>array(0,178,238),
	    "deepskyblue3"=>array(0,154,205),
	    "deepskyblue4"=>array(0,104,139),
	    "skyblue1"=>array(135,206,255),
	    "skyblue2"=>array(126,192,238),
	    "skyblue3"=>array(108,166,205),
	    "skyblue4"=>array(74,112,139),
	    "lightskyblue1"=>array(176,226,255),
	    "lightskyblue2"=>array(164,211,238),
	    "lightskyblue3"=>array(141,182,205),
	    "lightskyblue4"=>array(96,123,139),
	    "slategray1"=>array(198,226,255),
	    "slategray2"=>array(185,211,238),
	    "slategray3"=>array(159,182,205),
	    "slategray4"=>array(108,123,139),
	    "lightsteelblue1"=>array(202,225,255),
	    "lightsteelblue2"=>array(188,210,238),
	    "lightsteelblue3"=>array(162,181,205),
	    "lightsteelblue4"=>array(110,123,139),
	    "lightblue1"=>array(191,239,255),
	    "lightblue2"=>array(178,223,238),
	    "lightblue3"=>array(154,192,205),
	    "lightblue4"=>array(104,131,139),
	    "lightcyan1"=>array(224,255,255),
	    "lightcyan2"=>array(209,238,238),
	    "lightcyan3"=>array(180,205,205),
	    "lightcyan4"=>array(122,139,139),
	    "paleturquoise1"=>array(187,255,255),
	    "paleturquoise2"=>array(174,238,238),
	    "paleturquoise3"=>array(150,205,205),
	    "paleturquoise4"=>array(102,139,139),
	    "cadetblue1"=>array(152,245,255),
	    "cadetblue2"=>array(142,229,238),
	    "cadetblue3"=>array(122,197,205),
	    "cadetblue4"=>array(83,134,139),
	    "turquoise1"=>array(0,245,255),
	    "turquoise2"=>array(0,229,238),
	    "turquoise3"=>array(0,197,205),
	    "turquoise4"=>array(0,134,139),
	    "cyan1"=>array(0,255,255),
	    "cyan2"=>array(0,238,238),
	    "cyan3"=>array(0,205,205),
	    "cyan4"=>array(0,139,139),
	    "darkslategray1"=>array(151,255,255),
	    "darkslategray2"=>array(141,238,238),
	    "darkslategray3"=>array(121,205,205),
	    "darkslategray4"=>array(82,139,139),
	    "aquamarine1"=>array(127,255,212),
	    "aquamarine2"=>array(118,238,198),
	    "aquamarine3"=>array(102,205,170),
	    "aquamarine4"=>array(69,139,116),
	    "darkseagreen1"=>array(193,255,193),
	    "darkseagreen2"=>array(180,238,180),
	    "darkseagreen3"=>array(155,205,155),
	    "darkseagreen4"=>array(105,139,105),
	    "seagreen1"=>array(84,255,159),
	    "seagreen2"=>array(78,238,148),
	    "seagreen3"=>array(67,205,128),
	    "seagreen4"=>array(46,139,87),
	    "palegreen1"=>array(154,255,154),
	    "palegreen2"=>array(144,238,144),
	    "palegreen3"=>array(124,205,124),
	    "palegreen4"=>array(84,139,84),
	    "springgreen1"=>array(0,255,127),
	    "springgreen2"=>array(0,238,118),
	    "springgreen3"=>array(0,205,102),
	    "springgreen4"=>array(0,139,69),
	    "chartreuse1"=>array(127,255,0),
	    "chartreuse2"=>array(118,238,0),
	    "chartreuse3"=>array(102,205,0),
	    "chartreuse4"=>array(69,139,0),
	    "olivedrab1"=>array(192,255,62),
	    "olivedrab2"=>array(179,238,58),
	    "olivedrab3"=>array(154,205,50),
	    "olivedrab4"=>array(105,139,34),
	    "darkolivegreen1"=>array(202,255,112),
	    "darkolivegreen2"=>array(188,238,104),
	    "darkolivegreen3"=>array(162,205,90),
	    "darkolivegreen4"=>array(110,139,61),
	    "khaki1"=>array(255,246,143),
	    "khaki2"=>array(238,230,133),
	    "khaki3"=>array(205,198,115),
	    "khaki4"=>array(139,134,78),
	    "lightgoldenrod1"=>array(255,236,139),
	    "lightgoldenrod2"=>array(238,220,130),
	    "lightgoldenrod3"=>array(205,190,112),
	    "lightgoldenrod4"=>array(139,129,76),
	    "yellow1"=>array(255,255,0),
	    "yellow2"=>array(238,238,0),
	    "yellow3"=>array(205,205,0),
	    "yellow4"=>array(139,139,0),
	    "gold1"=>array(255,215,0),
	    "gold2"=>array(238,201,0),
	    "gold3"=>array(205,173,0),
	    "gold4"=>array(139,117,0),
	    "goldenrod1"=>array(255,193,37),
	    "goldenrod2"=>array(238,180,34),
	    "goldenrod3"=>array(205,155,29),
	    "goldenrod4"=>array(139,105,20),
	    "darkgoldenrod1"=>array(255,185,15),
	    "darkgoldenrod2"=>array(238,173,14),
	    "darkgoldenrod3"=>array(205,149,12),
	    "darkgoldenrod4"=>array(139,101,8),
	    "rosybrown1"=>array(255,193,193),
	    "rosybrown2"=>array(238,180,180),
	    "rosybrown3"=>array(205,155,155),
	    "rosybrown4"=>array(139,105,105),
	    "indianred1"=>array(255,106,106),
	    "indianred2"=>array(238,99,99),
	    "indianred3"=>array(205,85,85),
	    "indianred4"=>array(139,58,58),
	    "sienna1"=>array(255,130,71),
	    "sienna2"=>array(238,121,66),
	    "sienna3"=>array(205,104,57),
	    "sienna4"=>array(139,71,38),
	    "burlywood1"=>array(255,211,155),
	    "burlywood2"=>array(238,197,145),
	    "burlywood3"=>array(205,170,125),
	    "burlywood4"=>array(139,115,85),
	    "wheat1"=>array(255,231,186),
	    "wheat2"=>array(238,216,174),
	    "wheat3"=>array(205,186,150),
	    "wheat4"=>array(139,126,102),
	    "tan1"=>array(255,165,79),
	    "tan2"=>array(238,154,73),
	    "tan3"=>array(205,133,63),
	    "tan4"=>array(139,90,43),
	    "chocolate1"=>array(255,127,36),
	    "chocolate2"=>array(238,118,33),
	    "chocolate3"=>array(205,102,29),
	    "chocolate4"=>array(139,69,19),
	    "firebrick1"=>array(255,48,48),
	    "firebrick2"=>array(238,44,44),
	    "firebrick3"=>array(205,38,38),
	    "firebrick4"=>array(139,26,26),
	    "brown1"=>array(255,64,64),
	    "brown2"=>array(238,59,59),
	    "brown3"=>array(205,51,51),
	    "brown4"=>array(139,35,35),
	    "salmon1"=>array(255,140,105),
	    "salmon2"=>array(238,130,98),
	    "salmon3"=>array(205,112,84),
	    "salmon4"=>array(139,76,57),
	    "lightsalmon1"=>array(255,160,122),
	    "lightsalmon2"=>array(238,149,114),
	    "lightsalmon3"=>array(205,129,98),
	    "lightsalmon4"=>array(139,87,66),
	    "orange1"=>array(255,165,0),
	    "orange2"=>array(238,154,0),
	    "orange3"=>array(205,133,0),
	    "orange4"=>array(139,90,0),
	    "darkorange1"=>array(255,127,0),
	    "darkorange2"=>array(238,118,0),
	    "darkorange3"=>array(205,102,0),
	    "darkorange4"=>array(139,69,0),
	    "coral1"=>array(255,114,86),
	    "coral2"=>array(238,106,80),
	    "coral3"=>array(205,91,69),
	    "coral4"=>array(139,62,47),
	    "tomato1"=>array(255,99,71),
	    "tomato2"=>array(238,92,66),
	    "tomato3"=>array(205,79,57),
	    "tomato4"=>array(139,54,38),
	    "orangered1"=>array(255,69,0),
	    "orangered2"=>array(238,64,0),
	    "orangered3"=>array(205,55,0),
	    "orangered4"=>array(139,37,0),
	    "deeppink1"=>array(255,20,147),
	    "deeppink2"=>array(238,18,137),
	    "deeppink3"=>array(205,16,118),
	    "deeppink4"=>array(139,10,80),
	    "hotpink1"=>array(255,110,180),
	    "hotpink2"=>array(238,106,167),
	    "hotpink3"=>array(205,96,144),
	    "hotpink4"=>array(139,58,98),
	    "pink1"=>array(255,181,197),
	    "pink2"=>array(238,169,184),
	    "pink3"=>array(205,145,158),
	    "pink4"=>array(139,99,108),
	    "lightpink1"=>array(255,174,185),
	    "lightpink2"=>array(238,162,173),
	    "lightpink3"=>array(205,140,149),
	    "lightpink4"=>array(139,95,101),
	    "palevioletred1"=>array(255,130,171),
	    "palevioletred2"=>array(238,121,159),
	    "palevioletred3"=>array(205,104,137),
	    "palevioletred4"=>array(139,71,93),
	    "maroon1"=>array(255,52,179),
	    "maroon2"=>array(238,48,167),
	    "maroon3"=>array(205,41,144),
	    "maroon4"=>array(139,28,98),
	    "violetred1"=>array(255,62,150),
	    "violetred2"=>array(238,58,140),
	    "violetred3"=>array(205,50,120),
	    "violetred4"=>array(139,34,82),
	    "magenta1"=>array(255,0,255),
	    "magenta2"=>array(238,0,238),
	    "magenta3"=>array(205,0,205),
	    "magenta4"=>array(139,0,139),
	    "mediumred"=>array(140,34,34),         
	    "orchid1"=>array(255,131,250),
	    "orchid2"=>array(238,122,233),
	    "orchid3"=>array(205,105,201),
	    "orchid4"=>array(139,71,137),
	    "plum1"=>array(255,187,255),
	    "plum2"=>array(238,174,238),
	    "plum3"=>array(205,150,205),
	    "plum4"=>array(139,102,139),
	    "mediumorchid1"=>array(224,102,255),
	    "mediumorchid2"=>array(209,95,238),
	    "mediumorchid3"=>array(180,82,205),
	    "mediumorchid4"=>array(122,55,139),
	    "darkorchid1"=>array(191,62,255),
	    "darkorchid2"=>array(178,58,238),
	    "darkorchid3"=>array(154,50,205),
	    "darkorchid4"=>array(104,34,139),
	    "purple1"=>array(155,48,255),
	    "purple2"=>array(145,44,238),
	    "purple3"=>array(125,38,205),
	    "purple4"=>array(85,26,139),
	    "mediumpurple1"=>array(171,130,255),
	    "mediumpurple2"=>array(159,121,238),
	    "mediumpurple3"=>array(137,104,205),
	    "mediumpurple4"=>array(93,71,139),
	    "thistle1"=>array(255,225,255),
	    "thistle2"=>array(238,210,238),
	    "thistle3"=>array(205,181,205),
	    "thistle4"=>array(139,123,139),
	    "gray1"=>array(10,10,10),
	    "gray2"=>array(40,40,30),
	    "gray3"=>array(70,70,70),
	    "gray4"=>array(100,100,100),
	    "gray5"=>array(130,130,130),
	    "gray6"=>array(160,160,160),
	    "gray7"=>array(190,190,190),
	    "gray8"=>array(210,210,210),
	    "gray9"=>array(240,240,240),
	    "darkgray"=>array(100,100,100),
	    "darkblue"=>array(0,0,139),
	    "darkcyan"=>array(0,139,139),
	    "darkmagenta"=>array(139,0,139),
	    "darkred"=>array(139,0,0),
	    "silver"=>array(192, 192, 192),
	    "eggplant"=>array(144,176,168),
	    "lightgreen"=>array(144,238,144));		
    }
//----------------
// PUBLIC METHODS
    // Colors can be specified as either
    // 1. #xxxxxx			HTML style
    // 2. "colorname" 	as a named color
    // 3. array(r,g,b)	RGB triple
    // This function translates this to a native RGB format and returns an 
    // RGB triple.
    /**
     * Color
     * Insert description here
     *
     * @param $aColor
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function Color($aColor) {
	if (is_string($aColor)) {
	    // Strip of any alpha factor
	    $pos = strpos($aColor,'@');
	    if( $pos === false ) {
		$alpha = 0;
	    }
	    else {
		$pos2 = strpos($aColor,':');
		if( $pos2===false ) 
		    $pos2 = $pos-1; // Sentinel
		if( $pos > $pos2 ) {
		    $alpha = str_replace(',','.',substr($aColor,$pos+1));
		    $aColor = substr($aColor,0,$pos);
		}
		else {
		    $alpha = substr($aColor,$pos+1,$pos2-$pos-1);
		    $aColor = substr($aColor,0,$pos).substr($aColor,$pos2);
		}
	    }

	    // Extract potential adjustment figure at end of color
	    // specification
	    $pos = strpos($aColor,":");
	    if( $pos === false ) {
		$adj = 1.0;
	    }
	    else {
		$adj = 0.0 + str_replace(',','.',substr($aColor,$pos+1));
		$aColor = substr($aColor,0,$pos);
	    }
	    if( $adj < 0 )
		JpGraphError::RaiseL(25077);//('Adjustment factor for color must be > 0');

	    if (substr($aColor, 0, 1) == "#") {
		$r = hexdec(substr($aColor, 1, 2));
		$g = hexdec(substr($aColor, 3, 2));
		$b = hexdec(substr($aColor, 5, 2));
	    } else {
      		if(!isset($this->rgb_table[$aColor]) )
		    JpGraphError::RaiseL(25078,$aColor);//(" Unknown color: $aColor");
		$tmp=$this->rgb_table[$aColor];
		$r = $tmp[0];
		$g = $tmp[1];
		$b = $tmp[2];
	    }
	    // Scale adj so that an adj=2 always
	    // makes the color 100% white (i.e. 255,255,255. 
	    // and adj=1 neutral and adj=0 black.
	    if( $adj > 1 ) {
		$m = ($adj-1.0)*(255-min(255,min($r,min($g,$b))));
		return array(min(255,$r+$m), min(255,$g+$m), min(255,$b+$m),$alpha);
	    }
	    elseif( $adj < 1 ) {
		$m = ($adj-1.0)*max(255,max($r,max($g,$b)));
		return array(max(0,$r+$m), max(0,$g+$m), max(0,$b+$m),$alpha);
	    }
	    else {
		return array($r,$g,$b,$alpha);
	    }

	} elseif( is_array($aColor) ) {
	    if( count($aColor)==3 ) {
		$aColor[3]=0;
		return $aColor;
	    }
	    else
		return $aColor;
	}
	else
	    JpGraphError::RaiseL(25079,$aColor,count($aColor));//(" Unknown color specification: $aColor , size=".count($aColor));
    }
	
    // Compare two colors
    // return true if equal
    /**
     * Equal
     * Insert description here
     *
     * @param $aCol1
     * @param $aCol2
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function Equal($aCol1,$aCol2) {
	$c1 = $this->Color($aCol1);
	$c2 = $this->Color($aCol2);
	if( $c1[0]==$c2[0] && $c1[1]==$c2[1] && $c1[2]==$c2[2] )
	    return true;
	else
	    return false;
    }
	
    // Allocate a new color in the current image
    // Return new color index, -1 if no more colors could be allocated
    /**
     * Allocate
     * Insert description here
     *
     * @param $aColor
     * @param $aAlpha
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function Allocate($aColor,$aAlpha=0.0) {
	list ($r, $g, $b, $a) = $this->color($aColor);
	// If alpha is specified in the color string then this
	// takes precedence over the second argument
	if( $a > 0 )
	    $aAlpha = $a;
	if( $aAlpha < 0 || $aAlpha > 1 ) {
	    JpGraphError::RaiseL(25080);//('Alpha parameter for color must be between 0.0 and 1.0');
	}
	return imagecolorresolvealpha($this->img, $r, $g, $b, round($aAlpha * 127));
    }
} // Class

	
//===================================================
// CLASS Legend
// Description: Responsible for drawing the box containing
// all the legend text for the graph
//===================================================
DEFINE('_DEFAULT_LPM_SIZE',8);
/**
 * Legend
 * Insert description here
 *
 * @category
 * @package
 * @author
 * @copyright
 * @license
 * @version
 * @link
 * @see
 * @since
 */
class Legend {
    public $txtcol=array();
    private $color=array(0,0,0); // Default fram color
    private $fill_color=array(235,235,235); // Default fill color
    private $shadow=true; // Shadow around legend "box"
    private $shadow_color='darkgray@0.5';
    private $mark_abs_hsize=_DEFAULT_LPM_SIZE,$mark_abs_vsize=_DEFAULT_LPM_SIZE;
    private $xmargin=10,$ymargin=3,$shadow_width=2;
    private $xlmargin=2, $ylmargin='';
    private $xpos=0.05, $ypos=0.15, $xabspos=-1, $yabspos=-1;
    private $halign="right", $valign="top";
    private $font_family=FF_FONT1,$font_style=FS_NORMAL,$font_size=12;
    private $font_color='black';
    private $hide=false,$layout_n=1;
    private $weight=1,$frameweight=1;
    private $csimareas='';
    private $reverse = false ;
//---------------
// CONSTRUCTOR
    /**
     * Legend
     * Insert description here
     *
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function Legend() {
	// Empty
    }
//---------------
// PUBLIC METHODS	
    /**
     * Hide
     * Insert description here
     *
     * @param $aHide
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function Hide($aHide=true) {
	$this->hide=$aHide;
    }
	
    /**
     * SetHColMargin
     * Insert description here
     *
     * @param $aXMarg
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetHColMargin($aXMarg) {
	$this->xmargin = $aXMarg;
    }

    /**
     * SetVColMargin
     * Insert description here
     *
     * @param $aSpacing
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetVColMargin($aSpacing) {
	$this->ymargin = $aSpacing ;
    }

    /**
     * SetLeftMargin
     * Insert description here
     *
     * @param $aXMarg
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetLeftMargin($aXMarg) {
	$this->xlmargin = $aXMarg;
    }


    // Synonym
    /**
     * SetLineSpacing
     * Insert description here
     *
     * @param $aSpacing
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetLineSpacing($aSpacing) {
	$this->ymargin = $aSpacing ;
    }

    /**
     * SetShadow
     * Insert description here
     *
     * @param $aShow
     * @param $aWidth
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetShadow($aShow='gray',$aWidth=2) {
	if( is_string($aShow) ) {
	    $this->shadow_color = $aShow;
	    $this->shadow=true;
	}
	else
	    $this->shadow=$aShow;
	$this->shadow_width=$aWidth;
    }

    /**
     * SetMarkAbsSize
     * Insert description here
     *
     * @param $aSize
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetMarkAbsSize($aSize) {
	$this->mark_abs_vsize = $aSize ;
	$this->mark_abs_hsize = $aSize ;
    }

    /**
     * SetMarkAbsVSize
     * Insert description here
     *
     * @param $aSize
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetMarkAbsVSize($aSize) {
	$this->mark_abs_vsize = $aSize ;
    }

    /**
     * SetMarkAbsHSize
     * Insert description here
     *
     * @param $aSize
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetMarkAbsHSize($aSize) {
	$this->mark_abs_hsize = $aSize ;
    }

    /**
     * SetLineWeight
     * Insert description here
     *
     * @param $aWeight
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetLineWeight($aWeight) {
	$this->weight = $aWeight;
    }

    /**
     * SetFrameWeight
     * Insert description here
     *
     * @param $aWeight
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetFrameWeight($aWeight) {
	$this->frameweight = $aWeight;
    }
	
    /**
     * SetLayout
     * Insert description here
     *
     * @param $aDirection
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetLayout($aDirection=LEGEND_VERT) {
	$this->layout_n = $aDirection==LEGEND_VERT ? 1 : 99 ;
    }
	
    /**
     * SetColumns
     * Insert description here
     *
     * @param $aCols
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetColumns($aCols) {
	$this->layout_n = $aCols ;
    }

    /**
     * SetReverse
     * Insert description here
     *
     * @param $f
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetReverse($f=true) {
	$this->reverse = $f ;
    }

    // Set color on frame around box
    /**
     * SetColor
     * Insert description here
     *
     * @param $aFontColor
     * @param $aColor
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetColor($aFontColor,$aColor='black') {
	$this->font_color=$aFontColor;
	$this->color=$aColor;
    }
	
    /**
     * SetFont
     * Insert description here
     *
     * @param $aFamily
     * @param $aStyle
     * @param $aSize
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetFont($aFamily,$aStyle=FS_NORMAL,$aSize=10) {
	$this->font_family = $aFamily;
	$this->font_style = $aStyle;
	$this->font_size = $aSize;
    }
	
    /**
     * SetPos
     * Insert description here
     *
     * @param $aX
     * @param $aY
     * @param $aHAlign
     * @param $aVAlign
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetPos($aX,$aY,$aHAlign="right",$aVAlign="top") {
	$this->Pos($aX,$aY,$aHAlign,$aVAlign);
    }

    /**
     * SetAbsPos
     * Insert description here
     *
     * @param $aX
     * @param $aY
     * @param $aHAlign
     * @param $aVAlign
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetAbsPos($aX,$aY,$aHAlign="right",$aVAlign="top") {
	$this->xabspos=$aX;
	$this->yabspos=$aY;
	$this->halign=$aHAlign;
	$this->valign=$aVAlign;
    }


    /**
     * Pos
     * Insert description here
     *
     * @param $aX
     * @param $aY
     * @param $aHAlign
     * @param $aVAlign
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function Pos($aX,$aY,$aHAlign="right",$aVAlign="top") {
	if( !($aX<1 && $aY<1) )
	    JpGraphError::RaiseL(25120);//(" Position for legend must be given as percentage in range 0-1");
	$this->xpos=$aX;
	$this->ypos=$aY;
	$this->halign=$aHAlign;
	$this->valign=$aVAlign;
    }

    /**
     * SetFillColor
     * Insert description here
     *
     * @param $aColor
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetFillColor($aColor) {
	$this->fill_color=$aColor;
    }
	
    /**
     * Add
     * Insert description here
     *
     * @param $aTxt
     * @param $aColor
     * @param $aPlotmark
     * @param $aLinestyle
     * @param $csimtarget
     * @param $csimalt
     * @param $csimwintarget
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function Add($aTxt,$aColor,$aPlotmark='',$aLinestyle=0,$csimtarget='',$csimalt='',$csimwintarget='') {
	$this->txtcol[]=array($aTxt,$aColor,$aPlotmark,$aLinestyle,$csimtarget,$csimalt,$csimwintarget);
    }

    /**
     * GetCSIMAreas
     * Insert description here
     *
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function GetCSIMAreas() {
	return $this->csimareas;
    }
	
    /**
     * Stroke
     * Insert description here
     *
     * @param $aImg
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function Stroke(&$aImg) {
	// Constant
	$fillBoxFrameWeight=1;

	if( $this->hide ) return;

	$aImg->SetFont($this->font_family,$this->font_style,$this->font_size);		

	if( $this->reverse ) {
	    $this->txtcol = array_reverse($this->txtcol);
	}

	$n=count($this->txtcol);
	if( $n == 0 ) return;

	// Find out the max width and height of each column to be able
        // to size the legend box.
	$numcolumns = ($n > $this->layout_n ? $this->layout_n : $n);
	for( $i=0; $i < $numcolumns; ++$i ) {
	    $colwidth[$i] = $aImg->GetTextWidth($this->txtcol[$i][0]) +
		            2*$this->xmargin + 2*$this->mark_abs_hsize;
	    $colheight[$i] = 0;
	}

	// Find our maximum height in each row
	$rows = 0 ; $rowheight[0] = 0;
	for( $i=0; $i < $n; ++$i ) {
	    $h = max($this->mark_abs_vsize,$aImg->GetTextHeight($this->txtcol[$i][0]))+$this->ymargin;
	    if( $i % $numcolumns == 0 ) {
		$rows++;
		$rowheight[$rows-1] = 0;
	    }
	    $rowheight[$rows-1] = max($rowheight[$rows-1],$h);
	}

	$abs_height = 0;
	for( $i=0; $i < $rows; ++$i ) {
	    $abs_height += $rowheight[$i] ;
	}

	// Make sure that the height is at least as high as mark size + ymargin
	$abs_height = max($abs_height,$this->mark_abs_vsize);

	// We add 3 extra pixels height to compensate for the difficult in
	// calculating font height
	$abs_height += $this->ymargin+3; 
						
	// Find out the maximum width in each column
	for( $i=$numcolumns; $i < $n; ++$i ) {
	    $colwidth[$i % $numcolumns] = max(
		$aImg->GetTextWidth($this->txtcol[$i][0])+2*$this->xmargin+2*$this->mark_abs_hsize,$colwidth[$i % $numcolumns]);
	}

	// Get the total width
	$mtw = 0;
	for( $i=0; $i < $numcolumns; ++$i ) {
	    $mtw += $colwidth[$i] ;
	}

	// Find out maximum width we need for legend box
	$abs_width = $mtw+$this->xlmargin;

	if( $this->xabspos === -1  && $this->yabspos === -1 ) {
	    $this->xabspos = $this->xpos*$aImg->width ;
	    $this->yabspos = $this->ypos*$aImg->height ;
	}

	// Positioning of the legend box
	if( $this->halign == 'left' )
	    $xp = $this->xabspos; 
	elseif( $this->halign == 'center' )
	    $xp = $this->xabspos - $abs_width/2; 
	else  
	    $xp = $aImg->width - $this->xabspos - $abs_width;

	$yp=$this->yabspos;
	if( $this->valign == 'center' )
	    $yp-=$abs_height/2;
	elseif( $this->valign == 'bottom' )
	    $yp-=$abs_height;
			
	// Stroke legend box
	$aImg->SetColor($this->color);	
	$aImg->SetLineWeight($this->frameweight);
	$aImg->SetLineStyle('solid');

	if( $this->shadow )
	    $aImg->ShadowRectangle($xp,$yp,$xp+$abs_width+$this->shadow_width,
				   $yp+$abs_height+$this->shadow_width,
				   $this->fill_color,$this->shadow_width,$this->shadow_color);
	else {
	    $aImg->SetColor($this->fill_color);				
	    $aImg->FilledRectangle($xp,$yp,$xp+$abs_width,$yp+$abs_height);
	    $aImg->SetColor($this->color);							
	    $aImg->Rectangle($xp,$yp,$xp+$abs_width,$yp+$abs_height);
	}

	// x1,y1 is the position for the legend mark
	$x1=$xp+$this->mark_abs_hsize+$this->xlmargin;
	$y1=$yp + $this->ymargin;		
	
	$f2 =  round($aImg->GetTextHeight('X')/2);

	$grad = new Gradient($aImg);
	$patternFactory = null;

	// Now stroke each legend in turn
	// Each plot has added the following information to  the legend
	// p[0] = Legend text
	// p[1] = Color, 
	// p[2] = For markers a reference to the PlotMark object
	// p[3] = For lines the line style, for gradient the negative gradient style
	// p[4] = CSIM target
	// p[5] = CSIM Alt text
	$i = 1 ; $row = 0;
	foreach($this->txtcol as $p) {
	 
	    // STROKE DEBUG BOX
	    if( _JPG_DEBUG ) {
	        $aImg->SetLineWeight(1);
	        $aImg->SetColor('red');
	        $aImg->SetLineStyle('solid');
	        $aImg->Rectangle($xp,$y1,$xp+$abs_width,$y1+$rowheight[$row]);
	    }

	    $aImg->SetLineWeight($this->weight);
	    $x1 = round($x1); $y1=round($y1);
	    if ( !empty($p[2]) && $p[2]->GetType() > -1 ) {
		// Make a plot mark legend
		$aImg->SetColor($p[1]);
		if( is_string($p[3]) || $p[3]>0 ) {
		    $aImg->SetLineStyle($p[3]);
		    $aImg->StyleLine($x1-$this->mark_abs_hsize,$y1+$f2,$x1+$this->mark_abs_hsize,$y1+$f2);
		}
		// Stroke a mark with the standard size
		// (As long as it is not an image mark )
		if( $p[2]->GetType() != MARK_IMG ) {

		    // Clear any user callbacks since we ont want them called for
		    // the legend marks
		    $p[2]->iFormatCallback = '';
		    $p[2]->iFormatCallback2 = '';

		    // Since size for circles is specified as the radius
		    // this means that we must half the size to make the total
		    // width behave as the other marks
		    if( $p[2]->GetType() == MARK_FILLEDCIRCLE || $p[2]->GetType() == MARK_CIRCLE ) {
		        $p[2]->SetSize(min($this->mark_abs_vsize,$this->mark_abs_hsize)/2);
			$p[2]->Stroke($aImg,$x1,$y1+$f2);
		    }
		    else {
		        $p[2]->SetSize(min($this->mark_abs_vsize,$this->mark_abs_hsize));
			$p[2]->Stroke($aImg,$x1,$y1+$f2);
		    }
		}
	    } 
	    elseif ( !empty($p[2]) && (is_string($p[3]) || $p[3]>0 ) ) {
		// Draw a styled line
		$aImg->SetColor($p[1]);
		$aImg->SetLineStyle($p[3]);
		$aImg->StyleLine($x1-1,$y1+$f2,$x1+$this->mark_abs_hsize,$y1+$f2);
		$aImg->StyleLine($x1-1,$y1+$f2+1,$x1+$this->mark_abs_hsize,$y1+$f2+1);
	    } 
	    else {
		// Draw a colored box
		$color = $p[1] ;
		// We make boxes slightly larger to better show
		$boxsize = min($this->mark_abs_vsize,$this->mark_abs_hsize) + 2 ;
		$ym =  round($y1 + $f2 - $boxsize/2);
		// We either need to plot a gradient or a 
		// pattern. To differentiate we use a kludge.
		// Patterns have a p[3] value of < -100
		if( $p[3] < -100 ) { 
		    // p[1][0] == iPattern, p[1][1] == iPatternColor, p[1][2] == iPatternDensity
		    if( $patternFactory == null ) {
			$patternFactory = new RectPatternFactory();
		    }		    
		    $prect = $patternFactory->Create($p[1][0],$p[1][1],1);
		    $prect->SetBackground($p[1][3]);
		    $prect->SetDensity($p[1][2]+1);
		    $prect->SetPos(new Rectangle($x1,$ym,$boxsize,$boxsize));
		    $prect->Stroke($aImg);
		    $prect=null;
		}
		else {
		    if( is_array($color) && count($color)==2 ) {
			// The client want a gradient color
			$grad->FilledRectangle($x1,$ym,
					       $x1+$boxsize,$ym+$boxsize,
					       $color[0],$color[1],-$p[3]);
		    }
		    else {
			$aImg->SetColor($p[1]);
			$aImg->FilledRectangle($x1,$ym,$x1+$boxsize,$ym+$boxsize);
		    }
		    $aImg->SetColor($this->color);
		    $aImg->SetLineWeight($fillBoxFrameWeight);
		    $aImg->Rectangle($x1,$ym,$x1+$boxsize,$ym+$boxsize);
		}
	    }
	    $aImg->SetColor($this->font_color);
	    $aImg->SetFont($this->font_family,$this->font_style,$this->font_size);		
	    $aImg->SetTextAlign("left","top");			
	    $aImg->StrokeText(round($x1+$this->mark_abs_hsize+$this->xmargin),$y1,$p[0]);

	    // Add CSIM for Legend if defined
	    if( !empty($p[4]) ) {
		
		$xe = $x1 + $this->xmargin+$this->mark_abs_hsize+$aImg->GetTextWidth($p[0]);
		$ye = $y1 + max($this->mark_abs_vsize,$aImg->GetTextHeight($p[0]));
		$coords = "$x1,$y1,$xe,$y1,$xe,$ye,$x1,$ye";
		if( ! empty($p[4]) ) {
		    $this->csimareas .= "<area shape=\"poly\" coords=\"$coords\" href=\"".htmlentities($p[4])."\"";

		    if( !empty($p[6]) ) {
			$this->csimareas .= " target=\"".$p[6]."\"";
		    }

		    if( !empty($p[5]) ) {
			$tmp=sprintf($p[5],$p[0]);
			$this->csimareas .= " title=\"$tmp\" alt=\"$tmp\" ";
		    }
		    $this->csimareas .= " />\n";
		}
	    }
	    if( $i >= $this->layout_n ) {
		$x1 = $xp+$this->mark_abs_hsize+$this->xlmargin;
		$y1 += $rowheight[$row++];
		$i = 1;
	    }
	    else {
		$x1 += $colwidth[($i-1) % $numcolumns] ;
		++$i;
	    }
	}	
    }
} // Class
	

//===================================================
// CLASS DisplayValue
// Description: Used to print data values at data points
//===================================================
/**
 * DisplayValue
 * Insert description here
 *
 * @category
 * @package
 * @author
 * @copyright
 * @license
 * @version
 * @link
 * @see
 * @since
 */
class DisplayValue {
    public $margin=5;
    public $show=false;
    public $valign="",$halign="center";
    public $format="%.1f",$negformat="";
    private $ff=FF_FONT1,$fs=FS_NORMAL,$fsize=10;
    private $iFormCallback='';
    private $angle=0;
    private $color="navy",$negcolor="";
    private $iHideZero=false;

    /**
     * Show
     * Insert description here
     *
     * @param $aFlag
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function Show($aFlag=true) {
	$this->show=$aFlag;
    }

    /**
     * SetColor
     * Insert description here
     *
     * @param $aColor
     * @param $aNegcolor
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetColor($aColor,$aNegcolor="") {
	$this->color = $aColor;
	$this->negcolor = $aNegcolor;
    }

    /**
     * SetFont
     * Insert description here
     *
     * @param $aFontFamily
     * @param $aFontStyle
     * @param $aFontSize
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetFont($aFontFamily,$aFontStyle=FS_NORMAL,$aFontSize=10) {
	$this->ff=$aFontFamily;
	$this->fs=$aFontStyle;
	$this->fsize=$aFontSize;
    }

    /**
     * ApplyFont
     * Insert description here
     *
     * @param $aImg
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function ApplyFont($aImg) {
	$aImg->SetFont($this->ff,$this->fs,$this->fsize);
    }

    /**
     * SetMargin
     * Insert description here
     *
     * @param $aMargin
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetMargin($aMargin) {
	$this->margin = $aMargin;
    }

    /**
     * SetAngle
     * Insert description here
     *
     * @param $aAngle
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetAngle($aAngle) {
	$this->angle = $aAngle;
    }

    /**
     * SetAlign
     * Insert description here
     *
     * @param $aHAlign
     * @param $aVAlign
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetAlign($aHAlign,$aVAlign='') {
	$this->halign = $aHAlign;
	$this->valign = $aVAlign;
    }

    /**
     * SetFormat
     * Insert description here
     *
     * @param $aFormat
     * @param $aNegFormat
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetFormat($aFormat,$aNegFormat="") {
	$this->format= $aFormat;
	$this->negformat= $aNegFormat;
    }

    /**
     * SetFormatCallback
     * Insert description here
     *
     * @param $aFunc
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetFormatCallback($aFunc) {
	$this->iFormCallback = $aFunc;
    }

    /**
     * HideZero
     * Insert description here
     *
     * @param $aFlag
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function HideZero($aFlag=true) {
	$this->iHideZero=$aFlag;
    }

    /**
     * Stroke
     * Insert description here
     *
     * @param $img
     * @param $aVal
     * @param $x
     * @param $y
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function Stroke($img,$aVal,$x,$y) {
	
	if( $this->show ) 
	{
	    if( $this->negformat=="" ) $this->negformat=$this->format;
	    if( $this->negcolor=="" ) $this->negcolor=$this->color;

	    if( $aVal===NULL || (is_string($aVal) && ($aVal=="" || $aVal=="-" || $aVal=="x" ) ) ) 
		return;

	    if( is_numeric($aVal) && $aVal==0 && $this->iHideZero ) {
		return;
	    }

	    // Since the value is used in different cirumstances we need to check what
	    // kind of formatting we shall use. For example, to display values in a line
	    // graph we simply display the formatted value, but in the case where the user
	    // has already specified a text string we don't fo anything.
	    if( $this->iFormCallback != '' ) {
		$f = $this->iFormCallback;
		$sval = call_user_func($f,$aVal);
	    }
	    elseif( is_numeric($aVal) ) {
		if( $aVal >= 0 )
		    $sval=sprintf($this->format,$aVal);
		else
		    $sval=sprintf($this->negformat,$aVal);
	    }
	    else
		$sval=$aVal;

	    $y = $y-sign($aVal)*$this->margin;

	    $txt = new Text($sval,$x,$y);
	    $txt->SetFont($this->ff,$this->fs,$this->fsize);
	    if( $this->valign == "" ) {
		if( $aVal >= 0 )
		    $valign = "bottom";
		else
		    $valign = "top";
	    }
	    else
		$valign = $this->valign;
	    $txt->Align($this->halign,$valign);

	    $txt->SetOrientation($this->angle);
	    if( $aVal > 0 )
		$txt->SetColor($this->color);
	    else
		$txt->SetColor($this->negcolor);
	    $txt->Stroke($img);
	}
    }
}

//===================================================
// CLASS Plot
// Description: Abstract base class for all concrete plot classes
//===================================================
/**
 * Plot
 * Insert description here
 *
 * @category
 * @package
 * @author
 * @copyright
 * @license
 * @version
 * @link
 * @see
 * @since
 */
class Plot {
    public $numpoints=0;
    public $value;
    public $legend='';
    public $coords=array();
    public $color="black";
    public $hidelegend=false;
    public $line_weight=1;
    public $csimtargets=array(),$csimwintargets=array(); // Array of targets for CSIM
    public $csimareas="";			// Resultant CSIM area tags	
    public $csimalts=null;			// ALT:s for corresponding target
    public $legendcsimtarget='',$legendcsimwintarget='';
    public $legendcsimalt='';
    protected $weight=1;	
    protected $center=false;
//---------------
// CONSTRUCTOR
    /**
     * Plot
     * Insert description here
     *
     * @param $aDatay
     * @param $aDatax
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function Plot($aDatay,$aDatax=false) {
	$this->numpoints = count($aDatay);
	if( $this->numpoints==0 )
	    JpGraphError::RaiseL(25121);//("Empty input data array specified for plot. Must have at least one data point.");
	$this->coords[0]=$aDatay;
	if( is_array($aDatax) ) {
	    $this->coords[1]=$aDatax;
	    $n = count($aDatax);
	    for($i=0; $i < $n; ++$i ) {
		if( !is_numeric($aDatax[$i]) ) {
		    JpGraphError::RaiseL(25070);
		}
	    }
	}
	$this->value = new DisplayValue();
    }

//---------------
// PUBLIC METHODS	

    // Stroke the plot
    // "virtual" function which must be implemented by
    // the subclasses
    /**
     * Stroke
     * Insert description here
     *
     * @param $aImg
     * @param $aXScale
     * @param $aYScale
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function Stroke($aImg,$aXScale,$aYScale) {
	JpGraphError::RaiseL(25122);//("JpGraph: Stroke() must be implemented by concrete subclass to class Plot");
    }

    /**
     * HideLegend
     * Insert description here
     *
     * @param $f
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function HideLegend($f=true) {
	$this->hidelegend = $f;
    }

    /**
     * DoLegend
     * Insert description here
     *
     * @param $graph
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function DoLegend($graph) {
	if( !$this->hidelegend )
	    $this->Legend($graph);
    }

    /**
     * StrokeDataValue
     * Insert description here
     *
     * @param $img
     * @param $aVal
     * @param $x
     * @param $y
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function StrokeDataValue($img,$aVal,$x,$y) {
	$this->value->Stroke($img,$aVal,$x,$y);
    }
	
    // Set href targets for CSIM	
    /**
     * SetCSIMTargets
     * Insert description here
     *
     * @param $aTargets
     * @param $aAlts
     * @param $aWinTargets
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetCSIMTargets($aTargets,$aAlts='',$aWinTargets='') {
	$this->csimtargets=$aTargets;
	$this->csimwintargets=$aWinTargets;
	$this->csimalts=$aAlts;		
    }
 	
    // Get all created areas
    /**
     * GetCSIMareas
     * Insert description here
     *
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function GetCSIMareas() {
	return $this->csimareas;
    }	
	
    // "Virtual" function which gets called before any scale
    // or axis are stroked used to do any plot specific adjustment
    /**
     * PreStrokeAdjust
     * Insert description here
     *
     * @param $aGraph
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function PreStrokeAdjust($aGraph) {
	if( substr($aGraph->axtype,0,4) == "text" && (isset($this->coords[1])) )
	    JpGraphError::RaiseL(25123);//("JpGraph: You can't use a text X-scale with specified X-coords. Use a \"int\" or \"lin\" scale instead.");
	return true;	
    }
	
    // Get minimum values in plot
    /**
     * Min
     * Insert description here
     *
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function Min() {
	if( isset($this->coords[1]) )
	    $x=$this->coords[1];
	else
	    $x="";
	if( $x != "" && count($x) > 0 ) {
	    $xm=min($x);
	}
	else 
	    $xm=0;
	$y=$this->coords[0];
	$cnt = count($y);
	if( $cnt > 0 ) {
	    /*
	    if( ! isset($y[0]) ) {
		JpGraphError('The input data array must have consecutive values from position 0 and forward. The given y-array starts with empty values (NULL)');
	    }
	    $ym = $y[0];
	    */
	    $i=0;
	    while( $i<$cnt && !is_numeric($ym=$y[$i]) )
		$i++;
	    while( $i < $cnt) {
		if( is_numeric($y[$i]) ) 
		    $ym=min($ym,$y[$i]);
		++$i;
	    }			
	}
	else 
	    $ym="";
	return array($xm,$ym);
    }
	
    // Get maximum value in plot
    /**
     * Max
     * Insert description here
     *
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function Max() {
	if( isset($this->coords[1]) )
	    $x=$this->coords[1];
	else
	    $x="";

	if( $x!="" && count($x) > 0 )
	    $xm=max($x);
	else {
	    $xm = $this->numpoints-1;
	}
	$y=$this->coords[0];
	if( count($y) > 0 ) {
	    /*
	    if( !isset($y[0]) ) {
		JpGraphError::Raise('The input data array must have consecutive values from position 0 and forward. The given y-array starts with empty values (NULL)');
//		$y[0] = 0;
// Change in 1.5.1 Don't treat this as an error any more. Just silently convert to 0
// Change in 1.17 Treat his as an error again !! This is the right way to do !!
	    }
	    */
	    $cnt = count($y);
	    $i=0;
	    while( $i<$cnt && !is_numeric($ym=$y[$i]) )
		$i++;				
	    while( $i < $cnt ) {
		if( is_numeric($y[$i]) ) 
		    $ym=max($ym,$y[$i]);
		++$i;
	    }
	}
	else 
	    $ym="";
	return array($xm,$ym);
    }
	
    /**
     * SetColor
     * Insert description here
     *
     * @param $aColor
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetColor($aColor) {
	$this->color=$aColor;
    }
	
    /**
     * SetLegend
     * Insert description here
     *
     * @param $aLegend
     * @param $aCSIM
     * @param $aCSIMAlt
     * @param $aCSIMWinTarget
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetLegend($aLegend,$aCSIM='',$aCSIMAlt='',$aCSIMWinTarget='') {
	$this->legend = $aLegend;
	$this->legendcsimtarget = $aCSIM;
	$this->legendcsimwintarget = $aCSIMWinTarget;
	$this->legendcsimalt = $aCSIMAlt;
    }

    /**
     * SetWeight
     * Insert description here
     *
     * @param $aWeight
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetWeight($aWeight) {
	$this->weight=$aWeight;
    }
		
    /**
     * SetLineWeight
     * Insert description here
     *
     * @param $aWeight
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetLineWeight($aWeight=1) {
	$this->line_weight=$aWeight;
    }
	
    /**
     * SetCenter
     * Insert description here
     *
     * @param $aCenter
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetCenter($aCenter=true) {
	$this->center = $aCenter;
    }
	
    // This method gets called by Graph class to plot anything that should go
    // into the margin after the margin color has been set.
    /**
     * StrokeMargin
     * Insert description here
     *
     * @param $aImg
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function StrokeMargin($aImg) {
	return true;
    }

    // Framework function the chance for each plot class to set a legend
    /**
     * Legend
     * Insert description here
     *
     * @param $aGraph
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function Legend($aGraph) {
	if( $this->legend != "" )
	    $aGraph->legend->Add($this->legend,$this->color,"",0,$this->legendcsimtarget,
				 $this->legendcsimalt,$this->legendcsimwintarget);    
    }
	
} // Class


//===================================================
// CLASS PlotLine
// Description: 
// Data container class to hold properties for a static
// line that is drawn directly in the plot area.
// Usefull to add static borders inside a plot to show
// for example set-values
//===================================================
/**
 * PlotLine
 * Insert description here
 *
 * @category
 * @package
 * @author
 * @copyright
 * @license
 * @version
 * @link
 * @see
 * @since
 */
class PlotLine {
    public $scaleposition, $direction=-1; 
    protected $weight=1;
    protected $color="black";
    private $legend='',$hidelegend=false, $legendcsimtarget='', $legendcsimalt='',$legendcsimwintarget='';
    private $iLineStyle='solid';

//---------------
// CONSTRUCTOR
    /**
     * PlotLine
     * Insert description here
     *
     * @param $aDir
     * @param $aPos
     * @param $aColor
     * @param $aWeight
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function PlotLine($aDir=HORIZONTAL,$aPos=0,$aColor="black",$aWeight=1) {
	$this->direction = $aDir;
	$this->color=$aColor;
	$this->weight=$aWeight;
	$this->scaleposition=$aPos;
    }
	
//---------------
// PUBLIC METHODS	

    /**
     * SetLegend
     * Insert description here
     *
     * @param $aLegend
     * @param $aCSIM
     * @param $aCSIMAlt
     * @param $aCSIMWinTarget
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetLegend($aLegend,$aCSIM='',$aCSIMAlt='',$aCSIMWinTarget='') {
	$this->legend = $aLegend;
	$this->legendcsimtarget = $aCSIM;
	$this->legendcsimwintarget = $aCSIMWinTarget;
	$this->legendcsimalt = $aCSIMAlt;
    }

    /**
     * HideLegend
     * Insert description here
     *
     * @param $f
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function HideLegend($f=true) {
	$this->hidelegend = $f;
    }

    /**
     * SetPosition
     * Insert description here
     *
     * @param $aScalePosition
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetPosition($aScalePosition) {
	$this->scaleposition=$aScalePosition;
    }
	
    /**
     * SetDirection
     * Insert description here
     *
     * @param $aDir
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetDirection($aDir) {
	$this->direction = $aDir;
    }
	
    /**
     * SetColor
     * Insert description here
     *
     * @param $aColor
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetColor($aColor) {
	$this->color=$aColor;
    }
	
    /**
     * SetWeight
     * Insert description here
     *
     * @param $aWeight
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetWeight($aWeight) {
	$this->weight=$aWeight;
    }

    /**
     * SetLineStyle
     * Insert description here
     *
     * @param $aStyle
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function SetLineStyle($aStyle) {
	$this->iLineStyle = $aStyle;
    }

//---------------
// PRIVATE METHODS

    /**
     * DoLegend
     * Insert description here
     *
     * @param $graph
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function DoLegend(&$graph) {
	if( !$this->hidelegend )
	    $this->Legend($graph);
    }

    // Framework function the chance for each plot class to set a legend
    /**
     * Legend
     * Insert description here
     *
     * @param $aGraph
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function Legend(&$aGraph) {
	if( $this->legend != "" ) {
	    $dummyPlotMark = new PlotMark();
	    $lineStyle = 1;
	    $aGraph->legend->Add($this->legend,$this->color,$dummyPlotMark,$lineStyle,
				 $this->legendcsimtarget,$this->legendcsimalt,$this->legendcsimwintarget);    
	}
    }

    /**
     * PreStrokeAdjust
     * Insert description here
     *
     * @param $aGraph
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function PreStrokeAdjust($aGraph) {
	// Nothing to do
    }
	
    /**
     * Stroke
     * Insert description here
     *
     * @param $aImg
     * @param $aXScale
     * @param $aYScale
     *
     * @return
     *
     * @access
     * @static
     * @see
     * @since
     */
    function Stroke($aImg,$aXScale,$aYScale) {
	$aImg->SetColor($this->color);
	$aImg->SetLineWeight($this->weight);	
	$oldStyle = $aImg->SetLineStyle($this->iLineStyle);
	if( $this->direction == VERTICAL ) {
	    $ymin_abs=$aYScale->Translate($aYScale->GetMinVal());
	    $ymax_abs=$aYScale->Translate($aYScale->GetMaxVal());
	    $xpos_abs=$aXScale->Translate($this->scaleposition);
	    $aImg->StyleLine($xpos_abs, $ymin_abs, $xpos_abs, $ymax_abs);
	}
	elseif( $this->direction == HORIZONTAL ) {
	    $xmin_abs=$aXScale->Translate($aXScale->GetMinVal());
	    $xmax_abs=$aXScale->Translate($aXScale->GetMaxVal());
	    $ypos_abs=$aYScale->Translate($this->scaleposition);
	    $aImg->StyleLine($xmin_abs, $ypos_abs, $xmax_abs, $ypos_abs);
	}
	else {
	    JpGraphError::RaiseL(25125);//(" Illegal direction for static line");
	}
	$aImg->SetLineStyle($oldStyle);
    }
}

// <EOF>
?>