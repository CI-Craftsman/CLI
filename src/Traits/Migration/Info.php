<?php

namespace Craftsman\Traits\Migration;

/**
 *
 */
trait Info
{
  /**
   * Measure the queries execution time and show in console
   *
   * @param  array   $queries         CI Database queries
   * @param  boolean $show_in_console Hide/Show in the console mode
   * @return array                    Array of total exec time and the
   *                                  amount of queries.
   */
  protected function measureQueries(array $queries)
  {
      $migration_table = $this->migration->getTable();
      $query_exec_time = 0;
      $exec_queries    = 0;

      $this->newLine();

      for ($i = 0; $i < count($queries); $i++)
      {
          if ((! strpos($queries[$i], $migration_table))
              && (! strpos($queries[$i], $this->migration->db->database)))
          {
              $this->text(sprintf('<comment>-></comment> %s', $queries[$i]));

              $query_exec_time += $this->migration->db->query_times[$i];
              $exec_queries += 1;
          }
      }
      return array($query_exec_time, $exec_queries);
  }

  /**
   * Display in the console all the migration processes
   *
   * @param  string $signal           Migration command signal (++,--)
   * @param  float  $time_start       Unix timestamp with microseconds from the start of process
   * @param  float  $time_end         Unix timestamp with microseconds from the end of process
   * @param  float  $query_exec_time  Queries execution time in seconds
   * @param  int    $exec_queries     Amount of executed queries
   */
  protected function summary($signal, $time_start, $time_end, $query_exec_time, $exec_queries)
  {
      $this->newLine();
      $this->text(sprintf(
        '<info>%s</info> query process (%s s)', $signal, number_format($query_exec_time, 4)
      ));
      $this->newLine();
      $this->text(sprintf('<comment>%s</comment>', str_repeat('-', 30)));
      $this->newLine();
      $this->text(sprintf(
        '<info>++</info> finished in %s s', number_format(($time_end - $time_start), 4)
      ));
      $this->text(sprintf('<info>++</info> %s sql queries', $exec_queries));
  }
}
