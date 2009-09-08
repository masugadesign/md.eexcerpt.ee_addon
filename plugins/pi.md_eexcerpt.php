<?php
/* ============================================================================
ext.md_eexcerpt.php
Word limiting plugin that strips tags. 

Based on: 
pi.word_limit.php by Rick Ellis
pi.word_limit_plus.php by Vik Rubenfeld

INFO --------------------------------------------------------------------------
Developed by: Ryan Masuga, masugadesign.com
Created:   Feb 26 2007
Last Mod:  Sep 07 2009

CHANGELOG & OTHER INFO --------------------------------------------------------
See README.textile
=============================================================================== */

$plugin_info = array(
  'pi_name'        => 'MD Eexcerpt',
  'pi_version'     => '1.1.0',
  'pi_author'      => 'Ryan Masuga',
  'pi_author_url'  => 'http://masugadesign.com/',
  'pi_description' => 'Permits you to limit the number of words in some text. After stripping tags.',
  'pi_usage'       => Md_eexcerpt::usage()
);


Class Md_eexcerpt {

    var $return_data;

    // ----------------------------------------
    //  eexcerpt
    // ----------------------------------------

    function md_eexcerpt()
    {
        global $TMPL, $FNS;

    $stop_after = ( ! $TMPL->fetch_param('stop_after')) ? '500' :  $TMPL->fetch_param('stop_after');
    
    $if_Exceeds = ( ! $TMPL->fetch_param('if_exceeds')) ? '500' :  $TMPL->fetch_param('if_exceeds');
    $append = ( ! $TMPL->fetch_param('append')) ? '&hellip;' :  $TMPL->fetch_param('append');
    $the_link = ( ! $TMPL->fetch_param('the_link')) ? '' :  $TMPL->fetch_param('the_link');
    
    if ( ! is_numeric($stop_after))
      $stop_after = 500;
                
    if ( ! is_numeric($if_Exceeds))
      $if_Exceeds = 500;

    if ($if_Exceeds < $stop_after) 
    {
      $if_Exceeds = $stop_after;
    }
                
    $this->return_data = $this->_dirty_work($TMPL->tagdata, $if_Exceeds, $stop_after, $the_link, $append);
    }

    
    function _dirty_work($str, $if_Exceeds = 500, $stop_after = 500, $the_link = "", $append="")
    {
      global $TMPL;
      // strip out the tags first
      // http://us2.php.net/manual/en/function.strip-tags.php#68749
      $searchcrap = array('@<script[^>]*?>.*?</script>@si',  // Strip out javascript
               '@<[\\/\\!]*?[^<>]*?>@si',          // Strip out HTML tags
               '@<style[^>]*?>.*?</style>@siU',    // Strip style tags properly
               '@<![\\s\\S]*?--[ \\t\\n\\r]*>@'    // Strip multi-line comments including CDATA
               );

      $str = preg_replace($searchcrap, '', $str);


        if (strlen($str) < $stop_after) 
        {
            return $str;
        }
        
        $str = str_replace("\n", " ", $str);
        $str = preg_replace("/\s+/", " ", $str);
        $str = trim($str);
        $word = explode(" ", $str);
        $theCount = count($word);
        
        // if what you're counting is LESS than or equal to the stop after, or
        // LESS than or equal to the 'exceeeds' then there is nothing to do
        
        if (($theCount <= $stop_after) || ($theCount <= $if_Exceeds))
        {
          return $str;
        }
        // we have something to do. carry on...  
        $str = "";
                 
        for ($i = 0; $i < $stop_after; $i++) 
        {
          if (isset($word[$i])) 
          {
            $str .= $word[$i]." ";
          }
        }
        
        $str = trim($str); // trim again to get that last space

        if ($append != "") {$str .= $append;}
        if ($the_link != "") {$str .= $the_link;}

        return trim($str); 
    }
    // END	

    
// ----------------------------------------
//  Plugin Usage
// ----------------------------------------

// This function describes how the plugin is used.
//  Make sure and use output buffering

function usage()
{
ob_start(); 
?>
Wrap anything you want to be processed between the tag pairs. Works exactly like word_limit_plus, but strips tags.

{exp:md_eexcerpt if_exceeds="60" stop_after="40" append="&nbsp;&rarr;" the_link="<a href='{title_permalink=weblog/comments}'>MORE...</a>"}text you want processed{/exp:md_eexcerpt}

REFERENCE:  

if_exceeds -  [Required] Text will be truncated if it is greater than this number of words. This parameter must be included.

stop_after - [Required] Text greater than the number of words contained in the if_exceeds parameter, will be truncated to the word length stored in the stop_after parameter. This parameter must be included. Must be less than the number of words in the if_exceeds parameter.

append - [Optional] Will default to "&amp;hellip;", but you may change the output if desired. This will show before "the_link".

the_link - [Optional] A link back to the original article. When using this plugin with an RSS feed, I usually put the word "MORE" in the_link, with a link back to the original article. You can ignore this parameter if you don't want to use it.

<?php
$buffer = ob_get_contents();
ob_end_clean(); 
return $buffer;
}
// END

/* END class */
}