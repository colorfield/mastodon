/**
 * @file
 * Behaviors and utility functions for Mastodon admin UI.
 * Progressive enhancement of the Mastodon settings form.
 */

(function ($, Drupal, drupalSettings) {

  'use strict';

  /**
   * @namespace
   */
  Drupal.mastodon = {};

  Drupal.mastodon.getAuthorizationUrl = function() {
    var clientData = {
      'mastodon_instance': Drupal.mastodon.mastodon_instance,
      'application_name': Drupal.mastodon.application_name
    };
    $.ajax({
      type: 'GET',
      // @todo path from settings
      url: '/admin/mastodon/oauth/authorization-url',
      data: clientData,
      dataType: 'json',
      encode: true
    })
      .done(function (data) {
        // @todo set client_id and client_secret
        $('[data-drupal-selector="edit-get-authorization-url"]').html(data.authorization_url);
      });
  };

  Drupal.behaviors.mastodonAdminBehavior = {
    attach: function (context, drupalSettings) {
      $(context).find('#mastodon-settings-form').once('mastodonAdminBehavior').each(function () {

        // @todo check settings values, if client_id and client_secret and|or bearer already set
        // drupalSettings.mastodon.mastodon_instance
        // drupalSettings.mastodon.application_name
        // If they are not set, get them from the input text.
        Drupal.mastodon.mastodon_instance = $('[data-drupal-selector="edit-mastodon-instance"]').val();
        Drupal.mastodon.application_name = $('[data-drupal-selector="edit-application-name"]').val();

        var scopes = [];
        $('[data-drupal-selector="edit-scopes"] :checked').each(function() {
          scopes.push($(this).val());
        });
        Drupal.mastodon.scopes = scopes;

        Drupal.mastodon.client_id = $('[data-drupal-selector="edit-client-id"]').val();
        Drupal.mastodon.client_secret = $('[data-drupal-selector="edit-client-secret"]').val();

        // @todo if client_secret and client_id are not available
        // otherwise throw a warning that the token will need to be regenerated
        $('[data-drupal-selector="edit-get-authorization-url"]').on('click', function(e) {
          e.preventDefault();
          // get authorization URL
          if(Drupal.mastodon.mastodon_instance !== ''
            && Drupal.mastodon.application_name !== ''
            && Drupal.mastodon.client_id === ""
            && Drupal.mastodon.client_secret === "") {
            Drupal.mastodon.getAuthorizationUrl();
          }
        });

      });
    }
  };

})(jQuery, Drupal, drupalSettings);
