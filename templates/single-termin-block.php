<div class="wp-block-column relilab_termin_month">
   <?php echo RelilabTermine::lastpostmonthcheck(RelilabTermine::getMonat(get_post_meta(get_the_ID(), 'relilab_startdate',true))) ?>
</div>


<div class="wp-block-columns has-palette-color-5-background-color has-background">
    <div class="wp-block-column is-vertically-aligned-top" style="flex-basis:200px">
        <div class="wp-block-group relilab_termin_day">
            <p class="has-text-align-center">zur Live Veranstaltung</p>


            <p class="has-text-align-center"><?php echo RelilabTermine::getWochentag(get_post_meta(get_the_ID(), 'relilab_startdate',true)); ?></p>
            <p class="has-text-align-center"><?php echo date('j',strtotime(get_post_meta(get_the_ID(), 'relilab_startdate',true))).'.'; ?></p>
        <p class="has-text-align-center"><?php echo RelilabTermine::getMonat(get_post_meta(get_the_ID(), 'relilab_startdate',true)) .' '. date('Y', strtotime(get_post_meta(get_the_ID(),'relilab_startdate', true))); ?></p>
        <p class="has-text-align-center"><?php echo date('H:i',strtotime(get_post_meta(get_the_ID(),'relilab_startdate',true))) .' - '. date('H:i',strtotime(get_post_meta(get_the_ID(),'relilab_enddate',true)))?></p>

        </div>
    </div>

    <div class="wp-block-column">
        <?php if ( has_post_thumbnail() ) : ?>
            <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                <?php the_post_thumbnail('medium'); ?>
            </a>
        <?php endif ?>
        <br>
        <a class="has-text-align-center" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>

        <p><?php echo substr(get_the_excerpt(),0 ,150). ' ...'; ?>  </p>
    </div>
</div>
