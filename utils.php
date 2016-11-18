<?php

/**
 * Get the absolute path to the SAML hook file.
 * 
 * @global stdClass $CFG
 * @param string $path
 *   The path to the SAML hook file, either absolute or relative to the 
 *   directory root.
 * @return string
 *   The absolute path to the hook file.
 */
function resolve_samlhookfile($path) {
    global $CFG;

    if (strpos($path, '/') !== 0 && strpos($path, '\\') !== 0) {
        // This is a relative path because it doesn't with an / or an \.
        $path = $CFG->dirroot . DIRECTORY_SEPARATOR . $path;
    }

    return $path;
}
