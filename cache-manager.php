<?php
/**
 * Cache Manager
 * 
 * Handles the generation and management of the feature cache file
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

class AdvSet_CacheManager {
    /**
     * Get the source code of a function
     * 
     * @param callable $function The function to get the source from
     * @return string The function source code
     */
    private static function get_function_source($function) {
        try {
            $reflector = new ReflectionFunction($function);
            $filename = $reflector->getFileName();
            $startLine = $reflector->getStartLine();
            $endLine = $reflector->getEndLine();

            $code = file_get_contents($filename);
            $tokens = token_get_all($code);

            $functionCode = "";
            $insideFunction = false;
            $bracesCount = null;
            $currentLine = 1;

            foreach ($tokens as $token) {
                if (is_array($token)) {
                    $currentLine = $token[2]; // Zeilennummer aus dem Token
                    
                    // Funktion erst ab der Start-Zeile suchen
                    if ($currentLine < $startLine) {
                        continue;
                    }

                    // Start der gesuchten Funktion finden
                    if ($token[0] === T_FUNCTION) {
                        $insideFunction = true;
                    }
                }

                if ($insideFunction) {
                    $functionCode .= is_array($token) ? $token[1] : $token;
                    
                    // Geschweifte Klammern zählen
                    if ($token === '{') $bracesCount = ($bracesCount === null) ? 1 : $bracesCount + 1;
                    if ($token === '}') $bracesCount--;

                    // Ende der Funktion erreicht
                    if ($bracesCount === 0 && $insideFunction) {
                        break;
                    }
                }

                // Falls wir über das Ende der Funktion hinaus sind, abbrechen
                if ($currentLine > $endLine) {
                    break;
                }
            }

            return trim($functionCode);
        } catch (ReflectionException $e) {
            return false;
        }
    }
    
    /**
     * Generate the cache file with active features
     * 
     * @return bool Whether the file was successfully generated
     */
    public static function generate_cache_file() {
        $settings = get_option('advanced_settings_settings', []);
        advset_init_categories_and_features();
        
        // Get all features that are enabled
        $active_features = [];
        foreach ($settings as $feature_id => $feature_settings) {
            $feature = advset_get_feature($feature_id);
            if ($feature && isset($feature['handler_execute'])) {
                // For toggle features
                if (isset($feature_settings['enabled']) && $feature_settings['enabled']) {
                    $active_features[$feature_id] = [
                        'handler' => $feature['handler_execute'],
                        'settings' => $feature_settings
                    ];
                }
                // For other features that have any non-null settings
                else if (!isset($feature_settings['enabled']) && !empty($feature_settings)) {
                    $active_features[$feature_id] = [
                        'handler' => $feature['handler_execute'],
                        'settings' => $feature_settings
                    ];
                }
            }
        }
        
        // Start building the cache file content
        $content = "<?php\n";
        $content .= "/**\n";
        $content .= " * Advanced Settings Active Features\n";
        $content .= " * \n";
        $content .= " * AUTOMATICALLY GENERATED FILE - DO NOT EDIT!\n";
        $content .= " * Version: " . ADVSET_VERSION . "\n";
        $content .= " * Generated: " . date('Y-m-d H:i:s') . "\n";
        $content .= " * Settings Hash: " . md5(serialize($settings)) . "\n";
        $content .= " */\n\n";
        
        // Add security check
        $content .= "if (!defined('ABSPATH')) exit;\n\n";
        
        // Add settings as constants
        $content .= "// Feature Settings\n";
        foreach ($active_features as $feature_id => $feature) {
            $const_name = 'ADVSET_' . strtoupper(str_replace('.', '_', $feature_id)) . '_SETTINGS';
            $content .= "define('" . $const_name . "', " . var_export($feature['settings'], true) . ");\n";
        }
        $content .= "\n";
        
        // Add the execute function
        $content .= "function advset_execute_active_features() {\n";
        
        // Add each active feature's handler
        foreach ($active_features as $feature_id => $feature) {
            $content .= "\n    // Feature: " . $feature_id . "\n";
            
            // Get the function source
            $handler = $feature['handler'];
            if (is_string($handler)) {
                $content .= "    " . $handler . "();\n";
            } else {
                $source = self::get_function_source($handler);
                if ($source) {
                    // Add the function with settings
                    $const_name = 'ADVSET_' . strtoupper(str_replace('.', '_', $feature_id)) . '_SETTINGS';
                    $content .= "    call_user_func(" . $source . ", " . $const_name . ");\n";
                }
            }
        }
        
        $content .= "}\n";
        $content .= "\n";
        $content .= "return true;\n";
        
        // Try to write the file
        $result = file_put_contents(ADVSET_CACHE_FILE, $content);
        
        return $result !== false;
    }
    
    /**
     * Execute active features directly (fallback)
     */
    public static function execute_active_features_fallback() {
        $settings = get_option('advanced_settings_settings', []);
        advset_init_categories_and_features();
        
        foreach ($settings as $feature_id => $feature_settings) {
            $feature = advset_get_feature($feature_id);
            if ($feature && isset($feature['handler_execute'])) {
                // For toggle features
                if (isset($feature_settings['enabled']) && $feature_settings['enabled']) {
                    call_user_func($feature['handler_execute'], $feature_settings);
                }
                // For other features that have any non-null settings
                else if (!isset($feature_settings['enabled']) && !empty($feature_settings)) {
                    call_user_func($feature['handler_execute'], $feature_settings);
                }
            }
        }
    }
    
    /**
     * Clean up cache on plugin deactivation
     */
    public static function cleanup_cache() {
        if (file_exists(ADVSET_CACHE_FILE)) {
            unlink(ADVSET_CACHE_FILE);
        }
        
        // Try to remove cache directory if empty
        $cache_dir = dirname(ADVSET_CACHE_FILE);
        if (file_exists($cache_dir) && basename($cache_dir) === 'advanced-settings') {
            @rmdir($cache_dir);
        }
    }
} 