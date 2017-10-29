/**
 * @file
 * Behaviors and utility functions for Mastodon admin UI.
 */

(function ($, Drupal, drupalSettings) {

  'use strict';

  function getAuthorizationURL(instance, name) {

    var clientData = {
      'mastodon_instance': instance,
      'application_name': name
    };

    $.ajax({
      type        : 'GET',
      // @todo path from settings
      url         : '/admin/mastodon/oauth/authorization-url',
      data        : clientData,
      dataType    : 'json',
      encode      : true
    })
    // using the done promise callback
      .done(function(data) {
          // @todo implement, error handler
          console.log(data.authorization_url);
      });

  }

  Drupal.behaviors.mastodonAdminBehavior = {
    attach: function (context, drupalSettings) {
      $(context).find('#mastodon-settings-form').once('mastodonAdminBehavior').each(function () {
        if(drupalSettings.mastodon.mastodon_instance !== ''
          && drupalSettings.mastodon.application_name !== '') {
          getAuthorizationURL(
            drupalSettings.mastodon.mastodon_instance,
            drupalSettings.mastodon.application_name
          );
        }
      });
    }
  };

  /**
   * @namespace
   */
  Drupal.mastodon = {};

})(jQuery, Drupal, drupalSettings);
