<?php

class Hooks {

  private static array $actions = [];
  private static array $filters = [];

  // Add an action callback
  public static function add_action(string $hook, callable $callback, int $priority = 10): void {
    self::$actions[$hook][$priority][] = $callback;
  }

  // Run all action callbacks
  public static function do_action(string $hook, ...$args): void {
    if (empty(self::$actions[$hook])) return;

    ksort(self::$actions[$hook]);
    foreach (self::$actions[$hook] as $priority => $callbacks) {
      foreach ($callbacks as $callback) {
        call_user_func_array($callback, $args);
      }
    }
  }

  // Add a filter callback
  public static function add_filter(string $hook, callable $callback, int $priority = 10): void {
    self::$filters[$hook][$priority][] = $callback;
  }

  // Apply all filters to a value
  public static function apply_filters(string $hook, mixed $value, ...$args): mixed {
    if (empty(self::$filters[$hook])) return $value;

    ksort(self::$filters[$hook]);
    foreach (self::$filters[$hook] as $priority => $callbacks) {
      foreach ($callbacks as $callback) {
        $value = call_user_func_array($callback, array_merge([$value], $args));
      }
    }
    return $value;
  }

  // Remove a specific action
  public static function remove_action(string $hook, callable $callback, int $priority = 10): void {
    self::remove_callback(self::$actions, $hook, $callback, $priority);
  }

  // Remove a specific filter
  public static function remove_filter(string $hook, callable $callback, int $priority = 10): void {
    self::remove_callback(self::$filters, $hook, $callback, $priority);
  }

  // Utility to remove callback from action/filter array
  private static function remove_callback(array &$store, string $hook, callable $callback, int $priority): void {
    if (!isset($store[$hook][$priority])) return;

    foreach ($store[$hook][$priority] as $i => $existing_callback) {
      if ($existing_callback === $callback) {
        unset($store[$hook][$priority][$i]);
      }
    }

    // Clean up if empty
    if (empty($store[$hook][$priority])) {
      unset($store[$hook][$priority]);
    }
    if (empty($store[$hook])) {
      unset($store[$hook]);
    }
  }
}
