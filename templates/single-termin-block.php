<!-- wp:columns {"backgroundColor":"palette-color-5"} -->
<div class="wp-block-columns has-palette-color-5-background-color has-background"><!-- wp:column {"verticalAlignment":"center","width":"200px"} -->
    <div class="wp-block-column is-vertically-aligned-center" style="flex-basis:200px"><!-- wp:group {"className":"relilab_termin_day"} -->
        <div class="wp-block-group relilab_termin_day"><!-- wp:paragraph {"align":"center"} -->
            <p class="has-text-align-center">zur Live Veranstaltung</p>
            <!-- /wp:paragraph -->

            <!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"40px"}}} -->
            <p class="has-text-align-center" style="font-size:40px"><?php echo date('j',strtotime(get_post_meta(get_the_ID(), 'relilab_startdate',true))).'.'; ?></p>
            <!-- /wp:paragraph -->

            <!-- wp:paragraph {"align":"center"} -->
            <p class="has-text-align-center"></p>
            <!-- /wp:paragraph --></div>
        <!-- /wp:group -->

        <!-- wp:heading {"textAlign":"center","level":5} -->
        <h5 class="has-text-align-center">
            <?php echo getMonat(get_post_meta(get_the_ID(), 'relilab_startdate',true)) .' '. date('Y', strtotime(get_post_meta(get_the_ID(),'relilab_startdate', true))); ?>
        </h5>
        <!-- /wp:heading -->

        <!-- wp:paragraph {"align":"center"} -->
        <p class="has-text-align-center" ><?php echo getWochentag(get_post_meta(get_the_ID(), 'relilab_startdate',true)); ?></p>
        <!-- /wp:paragraph -->

        <!-- wp:paragraph {"align":"center"} -->
        <p class="has-text-align-center"><?php echo date('H:i',strtotime(get_post_meta(get_the_ID(),'relilab_startdate',true))) .' - '. date('H:i',strtotime(get_post_meta(get_the_ID(),'relilab_enddate',true)))?></p>
        <!-- /wp:paragraph -->

    </div>
    <!-- /wp:column -->

    <!-- wp:column -->
    <div class="wp-block-column">
        <?php if ( has_post_thumbnail() ) : ?>
            <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                <?php the_post_thumbnail('medium'); ?>
            </a>
        <?php endif ?>
        <br>
        <a class="has-text-align-center" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>

        <!-- wp:paragraph -->
        <p><?php the_excerpt(); ?>/p>
        <!-- /wp:paragraph --></div>
    <!-- /wp:column --></div>
<!-- /wp:columns -->