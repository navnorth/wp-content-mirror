<?php
require_once(OII_ECI_PATH . "classes/oii-eci-external-content.php");

class OII_ECI_Csv_Impoter
{
   function __construct(){

   }


    public function register_hooks(){
        add_action('admin_enqueue_scripts',array($this,'enqueue'));
        add_action('admin_menu', array( $this, 'create_menu_option') , 30);
        add_action('wp_ajax_my_action', array( $this, 'my_ajax_action_function'));
   }

    public function create_menu_option(){
        add_options_page( 'CSV Page Importer','CSV Page Importer','manage_options','csv-importer.php',array( $this, 'csv_import_form'));
      }

    public function csv_import_form(){
      $samplecsvfile = plugins_url('/assets/eci-import-sample.csv', dirname(__FILE__));
      $ajaxload = plugins_url('/assets/ajax-load.gif', dirname(__FILE__));

      echo '<div class="wrap">
            <div class="oese-csv-importer">
                <h2>WP External Importer</h2>
                  <div class="form-section">
                    <div class="container">
                        <div class="error_message"></div>
                        <form name="wp_importer" class="importer"  method="post" enctype="multipart/form-data">
                          <div class="cfile">
                           Choose File
                            <input type="file" accept=".csv" name="fileToUpload" id="fileToUpload">
                          </div>
                          <div class="cfile-upload">
                            <input type="button" class="button button-primary button-large" id="csv_upload" value="Upload Csv" name="submit">
                          </div>

                        </form>
                    </div>
                      <div class="c_file_name"></div>
                      <img style="display: none;" class="ajaxload" width="65" src="'.$ajaxload.'">
                      <div class="csv-sec">
                        <a class="csv_file" href="'.$samplecsvfile.'">Sample Csv</a>
                      </div>
                  </div>
                  <div class="results_table" style="display: none;">
                    <p class="page_count"></p>
                    <table class="fixed_header" id="page_result">
                      <thead>
                        <tr>
                          <th>Page Name</th>
                          <th>Action</th>
                        </tr>
                       </thead>
                    </table>

                  </div>
               </div>
            </div>';
    }

    public function enqueue(){
      //plugins_url('/assets/myscript.js', dirname(__FILE__) );
        wp_enqueue_script( 'my_custom_script', plugins_url('/js/oii-eci-csv-import-script.js', dirname(__FILE__)));

        wp_enqueue_style( 'my_plugin_styles', plugins_url('/css/oii-eci-csv-import-style.css', dirname(__FILE__)));

    }

    public function removeEverythingBefore($in, $before) {
      $pos = strpos($in, $before);
      return $pos !== FALSE
          ? substr($in, $pos + strlen($before), strlen($in))
          : "";
    }

    public function removeEverythingAfter($in, $after){
        $pos = strpos($in, $after);
        return $pos !== FALSE
        ? substr($in, 0, strpos($in, $after))
        :"";
    }


    public function getFilteredContentHtml($pageUrl,$startCode,$endCode){
      $arrContextOptions=array(
        "ssl"=>array(
            "verify_peer"=>false,
            "verify_peer_name"=>false,
        ),
      );
      $htmlPageContent = file_get_contents($pageUrl, false, stream_context_create($arrContextOptions));
      if($htmlPageContent === FALSE){
          if (!function_exists('curl_init')){
              die('CURL is not installed!');
          }

          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL, $pageUrl);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
          curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

          $output = curl_exec($ch);
          $htmlPageContent = $output;

         $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
         if($httpCode !== 200) {
            $this->insert_failed_import($pageUrl);
            //return false;
         }

         curl_close($ch);
      }

      $pTitle = preg_match('/<title[^>]*>(.*?)<\/title>/ims', $htmlPageContent, $matches) ? $matches[1] : null;

      $stringRight = $this->removeEverythingBefore($htmlPageContent,$startCode);
      $strLeft = $this->removeEverythingAfter($stringRight,$endCode);

      // Apply OII ECI External Content Replace
      $external_content = new OII_ECI_External_Content;
      $strLeft = $external_content->_replace_relative_links($strLeft, $pageUrl);
      $strLeft = $external_content->_apply_format($strLeft);

      return array('page-title' =>$pTitle , 'page-content'=>$strLeft);
    }

    public function createNewPage($pageName,$pageContent,$templateName,$pageCategory,$pageTag,$parentId, $metaDescription, $archiveDate, $pubID, $contactBox, $pageUrl){

          $template = "page-templates/".$templateName."-template.php";

          /** if content is empty, set page status to Draft **/
          $pageStatus = 'publish';
          if( empty($pageContent) || !empty($archiveDate) )  {
              $pageStatus = 'draft';
          }

          $post_data = array(
              'post_title'    => wp_strip_all_tags($pageName),
              'post_content'  => $pageContent,
              'post_status'   => $pageStatus,
              'post_type'     => 'page',
              'post_author'   => '1',
              'page_template' => ''
          );
          $pageId = wp_insert_post( $post_data, $error_obj );

          /**updating post parent **/

            if($parentId){
               if( get_post_status ($parentId)){
                  wp_update_post(
                      array(
                          'ID' => $pageId,
                          'post_parent' => $parentId
                      )
                  );
               }
            }

          /***Adding Category for page****/

          if($pageCategory){
              $pageCatArray = explode("|",$pageCategory);
              //print_r($mediaCatArray);
              foreach ($pageCatArray as $key => $catSlug) {
                  //$catSlug = str_replace(' ', '', $catSlug);
                  $pageCatObj =  get_category_by_slug($catSlug);
                  if($pageCatObj){
                    $catId = $pageCatObj->term_id;
                  }
                  else{
                    $catId = wp_create_category($catSlug);
                  }
                  wp_set_post_categories($pageId,array($catId),true);
              }

          }

          /***Adding Tags for page****/

          if($pageTag){
               $pageTagArray = explode(";",$pageTag);
               $pageTagArray = array_unique($pageTagArray);
              foreach ($pageTagArray as $key => $tagSlug) {
                  $tagIdObj = term_exists($tagSlug,"post_tag");
                  if($tagIdObj['term_id']){
                    wp_set_post_tags($pageId,array($tagSlug),true);
                  }
                  else{
                    $termObj = wp_insert_term($tagSlug,"post_tag");
                    if($termObj){
                      wp_set_post_tags($pageId,array($tagSlug),true);
                    }
                  }
              }
          }

         update_post_meta( $pageId, '_wp_page_template', $template);

         // check if Yoast is installed and active
         if(in_array('wordpress-seo/wp-seo.php', apply_filters('active_plugins', get_option('active_plugins')))){
            // add Yoast meta description if set
            if ($metaDescription)
               update_post_meta( $pageId, '_yoast_wpseo_metadesc', $metaDescription);
         }

         // update ACF fields
         if (function_exists('update_field')) {
            if ($archiveDate){
               update_field('archive_date', $archiveDate, $pageId);
            }
            if ($pubID){
               update_field('publication_id', $pubID, $pageId);
            }
            if ($pageUrl){
               update_field('source_URL', $pageUrl, $pageId);
            }
            if ($contactBox){
               update_field('ci_title', wp_strip_all_tags($pageName), $pageId);
               update_field('ci_email', 'contact_form', $pageId);
               update_field('ci_phone', '202-453-5563', $pageId);
               update_field('ci_address', 'For Direct Support, Contact Us', $pageId);
            }
         }

         $editLink = get_edit_post_link($pageId);
         return array('page_title' =>$pageName,'edit_link'=>$editLink);

    }


   public function my_ajax_action_function(){
      $csvImportFile = $_FILES['file']['tmp_name'];
      $csvAsArray = array_map('str_getcsv', file($csvImportFile));
      array_shift($csvAsArray);
      $output = array();
      foreach ($csvAsArray as $key => $csvVal) {
         $pageUrl = $csvVal[0];
         $pageStartCode = $csvVal[1];
         $pageEndCode = $csvVal[2];
         $pageTitle = $csvVal[3];
         $pageTemplate = $csvVal[4];
         $pageCategory = $csvVal[5];
         $pageTag = $csvVal[6];
         $parentId = $csvVal[7];
         $meta_description = $csvVal[8];
         $archive_date = $csvVal[9];
         $publication_id = $csvVal[10];
         $contactBox = $csvVal[11];

         if($pageUrl){
            $filteredHtml = $this->getFilteredContentHtml($pageUrl,$pageStartCode,$pageEndCode);

         if ($pageTitle=="")
            $pageTitle = $filteredHtml['page-title'];

            if($filteredHtml){
              $output[] = $this->createNewPage($pageTitle,$filteredHtml['page-content'],$pageTemplate,$pageCategory,$pageTag,$parentId, $meta_description, $archive_date, $publication_id, $contactBox, $pageUrl);
            }
         }

      }
      wp_send_json($output);
      die();

   }

   private function insert_failed_import($url){
      global $wpdb;

      $failed_url_exists = $wpdb->get_row( "SELECT * FROM ".$wpdb->prefix."eci_failed_imports WHERE Url = '". $url ."'" );

      if ( !$failed_url_exists ) {
         $wpdb->insert($wpdb->prefix."eci_failed_imports",
                       array('Url' => $url),
                       array('%s')
         );
      }
   }

}

if(class_exists('OII_ECI_Csv_Impoter')){
    $OII_ECI_Csv_Impoter = new OII_ECI_Csv_Impoter();
    $OII_ECI_Csv_Impoter->register_hooks();
}

?>
