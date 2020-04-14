(function ($, Drupal) {
  Drupal.behaviors.embargoes = {
    attach: function (context, settings) {
      $('div.field--type-file a').css('color', 'lightgray');
      $('div.field--type-file a').attr('title', 'Access to this file is restricted.');
    }
  };
})(jQuery, Drupal);
