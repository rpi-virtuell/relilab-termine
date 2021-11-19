<?php
include  'relilab-termine-ics.php';
/**
 * Plugin Name: relilab Termine
 * Plugin URI: https://github.com/rpi-virtuell/relilab-termine
 * Description: Erstellt Termine aus posts
 * Version: 1.0
 * Author: Daniel Reintanz
 * Licence: GPLv3
 */

class RelilabTermine{

    private static string $lastPostMonth = '';

    public function __construct(){
        add_shortcode('relilab_termine',array($this,'termineAusgeben'));
        add_action( 'wp_enqueue_scripts', array($this,'enqueue_scripts'));
        add_action('init','ical');
    }
    function   termineAusgeben( $atts ){

        $posts = array(
            'post_type'			=> 'post',
            'posts_per_page'	=> -1,
            'category_name'          => 'termine',
            'meta_key'			=> 'relilab_startdate',
            'meta_value'        =>  false,
            'meta_compare'      =>  '!=',
            'orderby'			=> 'meta_value',
            'order'				=> 'ASC',
        );
        $disable_all = $disable_cafe = $disable_talks = '';

        if(isset($_GET['cat'])){
          switch ($_GET['cat']){
              case 'relilab-talks':
                  $posts['category_name'] = 'relilab-talks';
                  $disable_talks = 'active';
                  break;
              case 'relilab-cafe':
                  $posts['category_name'] = 'relilab-cafe';
                  $disable_cafe = 'active';
                  break;
          }
        }else
            $disable_all = 'active';
        ob_start();
        ?>
        <div class="wp-block-column" >
            <a class="has-text-align-center button <?php echo  $disable_all ?>" href="<?php echo get_permalink(); ?>" ><?php echo 'Alle Termine' ?></a>
            <a class="has-text-align-center button <?php echo  $disable_talks ?>" href="<?php echo '?cat=relilab-talks'; ?>"><?php echo 'relilab-Talks' ?></a>
            <a class="has-text-align-center button <?php echo  $disable_cafe ?>" href="<?php echo '?cat=relilab-cafe'; ?>"><?php echo 'relilab-CAFÉ' ?></a>
            <a class="has-text-align-center button" href="<?php echo '?relilab-termine-format=ics'; ?>"> 📆 <?php echo 'ICS Datei herunterladen' ?></a>
        </div>
        <?php

            $posts=get_posts($posts);

global $post;
        ?>
        <ul>
            <?php
            foreach ($posts as $post) {
                setup_postdata( $post );
                {
                    if ($template = locate_template('relilab-Termine'))
                        load_template($template);
                    else
                        load_template(dirname(__FILE__) . '/templates/single-termin-block.php',false);
                }
            }
            ?>
        </ul>
        <?php
        wp_reset_postdata();
        return ob_get_clean();
    }
    function enqueue_scripts() {
     //   wp_enqueue_script( 'custom-js', plugin_dir_url( __FILE__ ) . 'js/custom.js', array( 'jquery' ), '', true );
        wp_enqueue_style( 'single-termin-block-style', plugin_dir_url( __FILE__ ) . 'css/style.css' );
    }
    /**
     * @param string $date
     * @return string
     */
    static function getWochentag(string $date): string{
        $wochentag = array(
            'Mon' => 'Montag',
            'Tue' => 'Dienstag',
            'Wed' => 'Mittwoch',
            'Thu' => 'Donnerstag',
            'Fri' => 'Freitag',
            'Sat' => 'Samstag',
            'Sun' => 'Sonntag',
        );
        return $wochentag[date('D',strtotime($date))];
    }

    /**
     * @param string $date
     * @return string
     */
    static function getMonat(string $date): string{
        $monat = array(
            'Jan'   => 'Januar',
            'Feb'   => 'Februar',
            'Mar'   => 'März',
            'Apr'   => 'April',
            'May'   => 'Mai',
            'Jun'   => 'Juni',
            'Jul'   => 'Juli',
            'Aug'   => 'August',
            'Sep'   => 'September',
            'Oct'   => 'Oktober',
            'Nov'   => 'November',
            'Dec'   => 'Dezember',
        );
        if(!empty($date))
            return $monat[date('M',strtotime($date))];
        else
            return '';
    }
    static function lastpostmonthcheck(string $currenPostMonth){
        if(self::$lastPostMonth == $currenPostMonth)
            $currenPostMonth = '';
        else
            self::$lastPostMonth = $currenPostMonth;
    return $currenPostMonth;
    }

}

$object = new RelilabTermine();
