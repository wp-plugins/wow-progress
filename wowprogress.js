jQuery().ready(function(){
	jQuery("#wowprogress .raid_head, #wowprogress .expansion_head").click(function(){
		jQuery(this).next().slideToggle("fast")
	})
})


