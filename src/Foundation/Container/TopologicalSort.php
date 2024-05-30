<?php

namespace Foundation\Container;

use SplStack;

class TopologicalSort
{
    /**
     * The keys are the vertices.
     * ​The values are the array of vertices adjacent to each corresponding vertex.
     *
     * @var array
     */
    protected $graph = [];

    /**
     * The keep track of vertices visited during graph traversal.
     *
     * @var array
     */
    protected $visited = [];

    /**
     * The stores the ending order of vertices during graph traversal.
     *
     * @var array|\SqlStack
     */
    protected $stack;

    public function __construct($graph)
    {
        $this->graph = $graph;
        $this->stack = new SplStack;
    }

    /**
     * Sort the vertices so that vertices with no dependencies 
     * are placed above those they depend on.
     *
     * @return array
     */
    public function sort()
    {
        foreach ($this->graph as $vertex => $neighbors) {
            if (! isset($this->visited[$vertex])) {
                $this->dfs($vertex);
            }
        }

        return array_reverse(iterator_to_array($this->stack));
    }

    /**
     * This function performs a Depth-First Search (DFS) on a graph from a given vertex, 
     * marking all reachable vertices as visited and pushing each vertex onto a stack 
     * once all its adjacent vertices have been fully explored.
     *
     * @param mixed $vertex
     * @return void
     */
    public function dfs($vertex)
    {
        $this->visited[$vertex] = true;

        if (isset($this->graph[$vertex])) {
            foreach ($this->graph[$vertex] as $neighbor) {
                if (! isset($this->visited[$neighbor])) {
                    $this->dfs($neighbor);
                }
            }
        }

        $this->stack->push($vertex);
    }
}
