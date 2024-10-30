<?php

if (!class_exists('LH_Mercury_web_parser_api_class')) {

class LH_Mercury_web_parser_api_class{
    
    var $api_key;
    var $post_type;
    var $api_url =  'https://mercury.postlight.com/parser';
    
    function isValidUrl($url) {
    $url = parse_url($url);
    if (!isset($url["host"])){
      
      return false;
      
    } else {
    
    return true;
    
    }
}
    
    private function mercury_to_post_array($body){
    
   $post_array = array(
				'comment_status'	=>	'closed',
				'ping_status'		=>	'closed',
				'post_title'		=>	$body->title,
				'post_status'		=>	'draft',
				'post_type'		=>	$this->post_type,
				'post_excerpt'		=>	$body->excerpt,
			);
    
        // If we've got Tidy, let's clean up input.
        // This step is highly recommended - PHP's default HTML parser
        // often doesn't do a great job and results in strange output.
        
        $html = $body->content;
        
if (function_exists('tidy_parse_string')) {
    

    

    
    $options = array("show-body-only" => true);
	$tidy = tidy_parse_string($html, $options, 'UTF8');
	$tidy->cleanRepair();
	$html = $tidy->value;

}

$post_array['post_content'] = $html;

return  $post_array;
    
} 


private function use_curl($url){
    

        $headers = array();
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");

$headers[] = "X-Api-Key: ".$this->api_key;
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$result = curl_exec($ch);
if (curl_errno($ch)) {
    echo 'Error:' . curl_error($ch);
}
curl_close ($ch);



return $result;

    
    
    
}
    
    public function action_url($url) {
        
        $url = add_query_arg( 'url', $url, $this->api_url);
        
        //echo $url;
        
        //echo $this->api_key;
        
        //$response = wp_remote_get( $url,  array( 'timeout' => 10, 'headers' => array( 'x-api-key' => 'foo'.$this->api_key, 'Content-Type' => 'application/json') ) );
        
        
$response = $this->use_curl($url);
        
        
        
        
        
        //print_r($response);
        
        //exit;
        
        //$body = json_decode($response['body']);
        
$body = json_decode($response);
        
        
        
        $post_array = $this->mercury_to_post_array($body);
        
        $post_id = wp_insert_post($post_array, true );
        
        if (isset($body->lead_image_url)){
            
            update_post_meta( $post_id, '_lh_mwpa_lead_image_url', $body->lead_image_url );
            
            
        }
        
        return $post_id;
        
    }
    
    public function __construct($api_key, $post_type = 'post') {
        
        /* Initialize api key */
        $this->api_key = $api_key;
        
         /* Initialize post type */
        $this->post_type = $post_type;
        
        
        
    } 

    
} 

}


?>