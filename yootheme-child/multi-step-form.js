jQuery(document).ready(function($) {
  var currentStep = 1;
  $('.form-step').hide();
  $('#step1').show();

  $('.next').click(function() {
    $('#step' + currentStep).hide();
    currentStep++;
    $('#step' + currentStep).show();
  });

  $('.prev').click(function() {
    $('#step' + currentStep).hide();
    currentStep--;
    $('#step' + currentStep).show();
  });
});