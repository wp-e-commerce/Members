
function suspendmembers(id,capability){
jQuery(document).ready(function(){
  var comm =jQuery("#"+capability+id).attr("checked");

  alert(comm);
  if (comm === true){
    jQuery.post("index.php","admin=true&ajax=true&members_caps=true&suspend=true&value=1&id="+id);
  } else {
    jQuery.post("index.php","admin=true&ajax=true&members_caps=true&suspend=true&value=2&id="+id);
  }
  //  jQuery.post('index.php?wpsc_admin_action=check_form_options',post_values, function(returned_data){
  return false;
  });
}


jQuery(document).ready(function(){

  if(!jQuery("#q_billing #q_billing_recurring").is(":checked")){
      jQuery("#recurring_options").hide(10);
      jQuery("#keep_charging").hide(10);
      jQuery("#charging_options").hide(10);
  }

  if(jQuery("#keep_charging #keep_charging_indefinitely").is(":checked")){
      jQuery("#charging_options").hide(10);
  }

  jQuery("#q_billing #q_billing_recurring").click(function(){
     jQuery("#recurring_options").show(10);
     jQuery("#keep_charging").show(10);
     if(!jQuery("#keep_charging input:radio").first().is(":checked")){
        jQuery("#charging_options").show(10);
     }
  });

  jQuery("#q_billing #q_billing_not_recurring").click(function(){
       jQuery("#recurring_options").hide(10);
       jQuery("#keep_charging").hide(10);
       jQuery("#charging_options").hide(10);
  });

  jQuery("#keep_charging #keep_charging_indefinitely").click(function(){
     jQuery("#charging_options").hide(10);
  });

  jQuery("#keep_charging #keep_charging_fixed").click(function(){
     jQuery("#charging_options").show(10);
  });

  jQuery("#wpsc_product_sub_type").change(function(){
     if (jQuery(this).is(":checked")){
        jQuery(this).parent().find("input:text").attr("disabled",true);
        jQuery(this).parent().find("select").attr("disabled",true);
     } else {
        jQuery(this).parent().find("input:text").removeAttr("disabled");
        jQuery(this).parent().find("select").removeAttr("disabled");
     }
  });

  jQuery("#wpsc_membership_length").change(function(){
        check_membership_length_valid();
  });

  jQuery("#wpsc_membership_length_unit").change(function(){
        check_membership_length_valid();
  });

});

function check_membership_length_valid(){
   error = false;

   if(jQuery("#wpsc_membership_length_unit").val()=="d"){
      max_membership_length = 1460;
      membership_length_unit = "days";

      if(jQuery("#wpsc_membership_length").val()>max_membership_length){
          jQuery("#wpsc_membership_length").val(max_membership_length);

          error = true;
      }
   } else if (jQuery("#wpsc_membership_length_unit").val()=="w"){
      max_membership_length = 208;
      membership_length_unit = "weeks";

      if(jQuery("#wpsc_membership_length").val()>max_membership_length){
          jQuery("#wpsc_membership_length").val(max_membership_length);

          error = true;
      }
   } else if (jQuery("#wpsc_membership_length_unit").val()=="m"){
      max_membership_length = 48;
      membership_length_unit = "months";

      if(jQuery("#wpsc_membership_length").val()>max_membership_length){
          jQuery("#wpsc_membership_length").val(max_membership_length);

          error = true;
      }
   } else if (jQuery("#wpsc_membership_length_unit").val()=="Y"){
      max_membership_length = 4;
      membership_length_unit = "years";

      if(jQuery("#wpsc_membership_length").val()>max_membership_length){
          jQuery("#wpsc_membership_length").val(max_membership_length);

          error = true;
      }
   }

   if (error === true){
      alert("You cannot add more than "+max_membership_length+" "+membership_length_unit+" as membership length.");
   }
}
