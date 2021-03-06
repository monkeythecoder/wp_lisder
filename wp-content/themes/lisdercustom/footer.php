	<!--</div><!-- #main .wrapper -->
	<!--<footer role="contentinfo" id="footer">
		<div class="row">
			<div class="large-12 columns">
				<div>
					<img src="<?php bloginfo('template_directory');?>/img/buap_s.png" alt="">
					<img src="<?php bloginfo('template_directory');?>/img/logofcc_s.png" alt="">
					<img src="<?php bloginfo('template_directory');?>/img/LISDER.png" alt="">

					<div class="copyright">
						<p>© 2013, Laboratorio de Sistemas Digitales y Energía Renovable BUAP. Todos los derechos reservados. </p>
					</div>

					<div class="social">
						<a href="https://twitter.com/LISDERFCC"><img src="<?php bloginfo('template_directory');?>/img/some_b.png" alt=""></a>
						<a href="https://www.facebook.com/LISDER"><img src="<?php bloginfo('template_directory');?>/img/fb1.png" alt=""></a>
					</div>
				</div>
			</div>
		</div>

	</footer> -->

	<script src="<?php bloginfo('template_directory');?>/js/vendor/jquery.js"></script>
	<script src="<?php bloginfo('template_directory');?>/js/foundation.min.js"></script>
	<?php 
	if(is_front_page()){
		?>
		<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=true"></script>
		<script src="<?php bloginfo('template_directory');?>/js/gmaps.js"></script>
		<script src="<?php bloginfo('template_directory');?>/js/scrollTo.min.js"></script>
		<script src="<?php bloginfo('template_directory');?>/js/orbit.js"></script>
		<script src="<?php bloginfo('template_directory');?>/js/front-functions.js"></script>
		<script>
		var height  = $("body").css("height")
		var scrolling = false;
		var scrollSize = height;

		$(document).ready(function(){
			
			document.addEventListener('keydown', function(event) {
				if(event.keyCode == 38) {
					event.preventDefault()
				}
				else if(event.keyCode == 40) {
					event.preventDefault()
				}
			})
			document.addEventListener('keyup', function(event) {
				if(event.keyCode == 38) {
					event.preventDefault()
					getSizeScroll()
					$.scrollTo( '-='+scrollSize, 900 );
				}
				else if(event.keyCode == 40) {
					event.preventDefault()
					getSizeScroll()
					$.scrollTo( '+='+scrollSize, 900 );
				}
			});
			var mousewheelevt=(/Firefox/i.test(navigator.userAgent))? "DOMMouseScroll" : "mousewheel" //FF doesn't recognize mousewheel as of FF3.x
 			if (document.attachEvent) //if IE (and Opera depending on user setting)
 				document.attachEvent("on"+mousewheelevt, function(e){
 					scrollPanel(e)

 				})
 			else if (document.addEventListener) //WC3 browsers
 				document.addEventListener(mousewheelevt, function(e){
 					scrollPanel(e)
 				}, false)

 			height  = $("body").css("height")
 			$('#down').click(function(){
 				$.scrollTo( '+='+scrollSize, 900 );
 			});

 			map = new GMaps({
 				div: '#mapa',
 				lat: 29.668291,
 				lng: -42.174454,
 				zoom: 3,
 				scrollwheel: false,
				//draggable: false
			});


 			<?php 
 			$args = array(
 				'post_type' => 'proyecto',
 				);

 			$the_query = new WP_Query( $args);
 			?>
 			<?php if( have_posts() ) : while ( $the_query->have_posts() ) : $the_query->the_post(); 
 			?>

 			map.addMarker({

 				lat: <?php echo get('latitud') ?>,

 				lng: <?php echo get('longitud') ?>,

 				title: '<?php the_title()?>',
 				icon: "<?php bloginfo('template_directory');?>/img/pin_2b.png" ,
 				infoWindow: {
 					content: '<h6><?php echo get("pais") ?></h6> <p><a href=" <?php the_permalink() ?> "><?php the_title()?></a></p>'
 				}
 			});

 			<?php endwhile; else: 
 			?>
 			<?php endif; 
 			?>


 			<?php 
 			$args = array(
 				'post_type' => 'publicacion',
 				);

 			$the_query = new WP_Query( $args);
 			?>
 			<?php if( have_posts() ) : while ( $the_query->have_posts() ) : $the_query->the_post(); 
 			?>

 			map.addMarker({
 				lat: <?php echo get('latitud') ?>,
 				lng: <?php echo get('longitud') ?>,
 				title: '<?php the_title()?>',
 				icon: "<?php bloginfo('template_directory');?>/img/pin_1b.png" ,
 				infoWindow: {
 					content: '<h6><?php echo get("pais") ?></h6> <p><a href="<?php echo get_site_url(); ?>/investigacion#id<?php the_id()?>"><?php the_title()?></a></p>'
 				}
 			});

 			<?php endwhile; else: 
 			?>
 			<?php endif; 
 			?>
 		});
	</script>
	<?php
}
?>

<?php
if (is_page("investigacion" )) {
	?>
	<script src="<?php bloginfo('template_directory');?>/js/isotope.js"></script>
	<script src="<?php bloginfo('template_directory');?>/js/isotopo.js"></script>
	<?php
}

if (is_page("integrantes")) {
	?>
	<script src="<?php bloginfo('template_directory');?>/js/orbit.js"></script>
	<script>
	$(document).foundation('orbit', {
		animation: 'fade',
		timer_speed: 10000,
		pause_on_hover: true,
		resume_on_mouseout: false,
		animation_speed: 500,
		stack_on_small: true,
		navigation_arrows: true,
		slide_number: false,
		container_class: 'orbit-container',
		stack_on_small_class: 'orbit-stack-on-small',
		next_class: 'orbit-next',
		prev_class: 'orbit-prev',
		timer_container_class: 'orbit-timer',
		timer_paused_class: 'paused',
		timer_progress_class: 'orbit-progress',
		slides_container_class: 'orbit-slides-container',
		bullets_container_class: 'orbit-bullets',
		bullets_active_class: 'active',
		slide_number_class: 'orbit-slide-number',
		caption_class: 'orbit-caption',
		active_slide_class: 'active',
		orbit_transition_class: 'orbit-transitioning',
		bullets: false,
		timer: false,
		next_on_click: false,
		variable_height: false,
		before_slide_change: function(){},
		after_slide_change: function(){}
	});
	</script>
	<?php
}
?>
<?php wp_footer(); ?>

</body>
</html>