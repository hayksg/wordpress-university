<?php get_header(); ?>

<?php while(have_posts()) : ?>
	<?php the_post(); ?>

	<?php pageBanner(); ?>

	<div class="container container--narrow page-section">
		<div class="metabox metabox--position-up metabox--with-home-link">
			<p>
				<a class="metabox__blog-home-link" href="<?php echo get_post_type_archive_link('program'); ?>">
					<i class="fa fa-home" aria-hidden="true"></i> All Programs
				</a>
				<span class="metabox__main"><?php the_title(); ?></span>
			</p>
		</div>
		<div class="generic-content"><?php the_field('main_body_content'); ?></div>

		<?php

		$today = date( 'Ymd' );

		$relatedProfessors = new WP_Query( array(
			'posts_per_page' => -1,
			'post_type'      => 'professor', // custom post type
			'orderby'        => 'title',
			'order'          => 'ASC',
			'meta_query'     => array(
				array(
					'key' => 'related_programs',
					'compare' => 'LIKE',
					'value'   => '"' . get_the_ID() . '"',
					'type'    => 'string'
				)
			),
		) );

		?>

		<?php if ($relatedProfessors->have_posts()) : ?>

            <hr class="section-break">
            <h2 class="headline headline--medium"><?php echo get_the_title(); ?> Professors</h2>

            <ul class="professor-cards">
			<?php while ( $relatedProfessors->have_posts() ) : $relatedProfessors->the_post(); ?>

				<li class="professor-card__list-item">
                    <a href="<?php the_permalink(); ?>" class="professor-card">

                        <img class="professor-card__image" src="<?php the_post_thumbnail_url( 'professorLandscape' ) ?>">
                        <span class="professor-card__name"><?php the_title(); ?></span>

                    </a>
                </li>

			<?php endwhile; ?>
            </ul>

		<?php endif; ?>

        <?php wp_reset_postdata(); ?>

		<?php

		$today = date( 'Ymd' );

		$homepageEvents = new WP_Query( array(
			'posts_per_page' => 2,
			'post_type'      => 'event', // custom post type
			'meta_key'       => 'event_date',
			'orderby'        => 'meta_value_num',
			'order'          => 'ASC',
			// do not show any past events
			'meta_query'     => array(
				array(
					'key' => 'event_date',
					'compare' => '>=',
					'value'   => $today,
					'type'    => 'numeric'
				),
				array(
					'key' => 'related_programs',
					'compare' => 'LIKE',
					'value'   => '"' . get_the_ID() . '"',
					'type'    => 'string'
				)
			),
		) );

		?>

		<?php if ($homepageEvents->have_posts()) : ?>

			<hr class="section-break">
			<h2 class="headline headline--medium">Upcoming <?php echo get_the_title(); ?> Events</h2>

			<?php while ( $homepageEvents->have_posts() ) : $homepageEvents->the_post(); ?>

                <!--Will include 'template-parts/content-event'-->
				<?php get_template_part( 'template-parts/content', 'event' ); ?>

			<?php endwhile; ?>

		<?php endif; ?>

        <hr class="section-break">

        <!-- Related Campuses -->

        <?php
            wp_reset_postdata();
            $relatedCampuses = get_field('related_campus');
        ?>

        <?php if ($relatedCampuses) : ?>
            <h2 class="headline headline--medium"><?php echo get_the_title(); ?> is Available At These Campuses:</h2>

            <ul class="min-list link-list">
            <?php foreach ($relatedCampuses as $campus) : ?>
                <li><a href="<?php echo get_the_permalink($campus); ?>"><?php echo get_the_title($campus); ?></a></li>
            <?php endforeach; ?>
            </ul>
        <?php endif; ?>

	</div>

<?php endwhile; ?>

<?php get_footer(); ?>