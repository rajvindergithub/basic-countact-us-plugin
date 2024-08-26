<?php 

/*
 * Plugin Name:       Contact US Form Basic Plugin
 * Description:       Basic plugin for manage contact form records.
 * Version:           1.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Rajvinder Singh
 * Text Domain:       contact-form-basic
 * Domain Path:       /languages
 */


if( !defined('ABSPATH'))
    {
        exit; 
    }

class SimpleContactForm{
    
    public function __construct(){
        
        
        //create custom post type
         add_action('init', array($this, 'create_custom_post_type'));
        
        //add assets
        add_action('wp_enqueue_scripts', array($this, 'load_assets'));
        
        // create shortcode 
        add_shortcode('contact-form', array($this, 'load_shortcode'));
        
        
        //add script 
        add_action('wp_footer', array($this, 'load_scripts'));
        
        add_action('rest_api_init', array($this, 'register_rest_api'));
    }
        
    public function create_custom_post_type(){
       
        $args = array(
            'public' => true, 
            'has_archive' => true, 
            'supports' => array('title','editor'),
            'exclude_from_search' => true, 
            'publicly_queryable' => true,
            'capability' => 'manage_options',
            'labels' => array(
                'name' => 'Contact Form',
                'singular_name' => 'Contact Form Entry',
            ),
            'menu_icon' => 'dashicons-media-text',
        );
        
        register_post_type('simple_contact_form',$args);
        
    }
    
    
    public function load_assets(){
            wp_enqueue_style(
                'simple-contact-form', plugin_dir_url( __FILE__ ).'/css/style.css', 
                array(), 
                1 , 
                'all' 
            );
        
            wp_enqueue_script(
                'simple-contact-form', plugin_dir_url( __FILE__ ).'/js/script.js', 
                array('jquery'), 
                1 , 
                true
            );     
        
        }
    
  
    
    public function load_shortcode(){?>

        <section id="contact_us_form" class="contact_us_form" style="width: 50%; max-width: 100%; margin: 5% auto; ">
            
            <h2>Send us an email</h2>
            <p>Please fill the below details</p>
            
            <form id="simple-contact-form_form">
                <div class="form-group mb-2">
                    <input type="text" name="name" placeholder="Name" class="form-control" />
                </div>
                <div class="form-group mb-2">
                    <input type="email" name="email" placeholder="Email" class="form-control" />
                </div>
                <div class="form-group mb-2">
                    <input type="tel" name="phone" placeholder="Phone" class="form-control" />
                </div>
                <div class="form-group mb-2">
                    <textarea name="message" placeholder="Type your message ... " class="form-control"></textarea>
                </div>
                <div class="form-group mb-2">
                    <button class="btn btn-success btn-block w-100" type="submit">Send Message</button>
                </div>
                <div id="mail_response"></div>
            </form>
            
        </section>

         
    <?php
                                    
}

    public function load_scripts(){ ?>

            <script>
                
                let nonce = '<?php echo wp_create_nonce('wp_rest'); ?>';
                
                    jQuery('#simple-contact-form_form').submit(function(e){
                        
                        e.preventDefault(); 
                        
                        var getFormData = jQuery(this).serialize();
                        
                        console.log(getFormData);
                        
                        $.ajax({
                            method: 'POST',
                            url : '<?php echo get_rest_url(null, 'simple-contact-form/v1/send-email');?>',
                            headers : { 'X-WP-Nonce' : nonce },
                            data: getFormData,
                            success: function(res){
                                console.log('a');
                                console.log(document.cookie);
                                jQuery('#mail_response').html('Mail Send.');
                            },
                            error: function(error){
                                console.log('hello');
                                console.log(error.responseText);
                                jQuery('#mail_response').html(error.responseText);
                            }
                        });
                        
                    });
            </script>

            
        <?php }
    
    
    public function register_rest_api(){
        
        register_rest_route('simple-contact-form/v1','send-email', array(
        
            'methods' => 'POST',
            'callback' => array($this, 'handle_contact_form')
            
        ));    
    }
    
    public function handle_contact_form($data){
         $headers = $data->get_headers();
         $params = $data->get_params();
        $nonce = $headers['x_wp_nonce'][0];
        
        if(!wp_verify_nonce($nonce, 'wp_rest')){ 
            echo 'Not Defined'; 
        } 
        
         
        
        $message =  'Name of Sender : '.$params['name'].'<br />';
        $message .= 'Email of Sender : '.$params['email'].'<br />';
        $message .= 'Phone Number of Sender : '.$params['phone'].'<br />';
        $message .= 'Message of Sender : <br />'.$params['message'].'<br />';
        
        $post_id = wp_insert_post([
            'post_type' => 'simple_contact_form',
            'post_title' => 'Email From '.$params['name'], 
            'post_status' => 'publish',
            'post_content' => $message,
        ]);
        
        if($post_id){
           
            $post_status = 'Message';
            return new WP_REST_Response('Thankyou for email ', 200);
          
        }
        
        
    }
    
}

new SimpleContactForm; 

//echo plugin_dir_url( __FILE__ ); 


   

?>

