/**
 * Common functions for reportbuilder used by both front-end and back-end
 * @author Alexander Gilmanov
 * @since 30.03.2016
 */

/**
 * Helper function to get the cookie
 */
function wdtGetCookie( name ) {
    var parts = document.cookie.split(name + "=");
    if (parts.length == 2) return parts.pop().split(";").shift();
}

/**
 * Helper function to remove the cookie
 */
function wdtExpireCookie( cName ) {
    document.cookie =
        encodeURIComponent( cName ) +
        "=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/";
}