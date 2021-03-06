<?php
get_header();
?>

<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

	<?php $url = wp_get_attachment_url( get_post_thumbnail_id($post->ID) ); ?>
	
	<div class="row">
		<div class="large-12 columns">
			<ul class="breadcrumbs">
				<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Inicio</a></li>
				<li><a href="<?php echo get_site_url(); ?>/noticias">Noticias</a></li>
				<li class="current"><a href="#"><?php the_title(  ) ?></a></li>
			</ul>
		</div>
	</div>

	<div id="proyecto-container" class="entrada-container" style="background: url('<?php echo $url?>') no-repeat center center fixed; -webkit-background-size: cover; -moz-background-size: cover; -o-background-size: cover; background-size: cover;">


		<div class="layer-proyecto">
			<div class="row">

				<div class="large-12 columns titulo">
					<h2><?php the_title(  ) ?></h2>
				</div>

				<div class="large-12 columns descripcion">
					<h5 class="date"><?php the_date( ); ?></h5>
				</div>
			</div>
		</div>
	</div>


	<div class="row">
		<div class="large-12 columns contenido-proyecto">
			<?php the_content(  ) ?>
		</div>
	</div>


	<?php endwhile; else: 
	?>
	<?php endif; 
	?>

	<?php 
	get_footer(); 
	?>