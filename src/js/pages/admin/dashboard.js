define('pages/admin/dashboard', function() {
	var analytics = $('.analytics .content');
	var workspace = $.svg.workspace();
	var circle = $.svg.circle(100, 100);
	var circleBackground = $.svg.circle(100, 100);
	var rad = 42.5;
	var percent = 80;
	var offset = ((100 - percent) / 100) * (Math.PI * (rad * 2));
	
	workspace.viewBox(0, 0, 256, 100);
	
	circleBackground.fill('transparent');
	circleBackground.stroke('#4f4f4f');
	circleBackground.strokeWidth(15);
	circleBackground.dashArray(Math.PI * (rad * 2));
	circleBackground.pos(50, 50);
	circleBackground.rad(rad);
	
	circle.fill('transparent');
	circle.stroke('#4698ff');
	circle.strokeWidth(15);
	circle.dashArray(Math.PI * (rad * 2));
	circle.dashOffset(offset);
	circle.pos(50, 50);
	circle.rad(rad);
	
	workspace.append(circleBackground);
	workspace.append(circle);
	//analytics.append(workspace);
	
	/*var chart = $.svg.chart.line({
		width: 100,
		height: 100,
		
		labels: [1, 2, 3, 4, 5]
		
	});*/
	
	//analytics.append(chart);
	
	var navigationToggle =$('.navigation-toggle');
	
	navigationToggle.click(function(e) {
		navigationToggle.toggleClass('active');
		$('body').toggleClass('navigation-open');
	});
});