<?php

if (!class_exists('LH_Readability_parser_class')) {

class LH_Readability_parser_class{
    
    
 static function random_user_agent(){
$agents=array(
'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.2 (KHTML, like Gecko) Chrome/22.0.1216.0 Safari/537.2',
'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.133 Safari/537.36',
'Mozilla/1.22 (compatible; MSIE 10.0; Windows 3.1)',
'Mozilla/5.0 (Windows NT 6.3; Trident/7.0; rv:11.0) like Gecko',
'Opera/9.80 (Windows NT 6.0) Presto/2.12.388 Version/12.14',
'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_3) AppleWebKit/537.75.14 (KHTML, like Gecko) Version/7.0.3 Safari/7046A194A');
 
  $chose=rand(0,5);
  return $agents[$chose];
}
    
    
public static function get_readable_content($html, $url){

if (!class_exists('Readability')) {

require_once 'readability/readability.php';


}

// give it to Readability
$readability = new Readability($html, $url);
// print debug output? 
// useful to compare against Arc90's original JS version - 
// simply click the bookmarklet with FireBug's console window open
$readability->debug = false;
// convert links to footnotes?
$readability->convertLinksToFootnotes = true;
// process it
$result = $readability->init();
// does it look like we found what we wanted?

if ($result) {

$return['title'] = $readability->getTitle()->textContent;
$return['body'] = $readability->getContent()->innerHTML;

return $return;

} else {
    
return false;

    
}


}



public static function action_url($url) {
    

    
    $response = wp_remote_get( $url,  array( 'timeout' => 10, 'user-agent'=> self::random_user_agent() ));
    

    
    $return = self::get_readable_content($response['body'], $url);
    
    
    
return $return;
        
    
    
    
} 

    
    
    
} 

}


?>