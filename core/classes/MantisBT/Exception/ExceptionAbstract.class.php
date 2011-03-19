<?php
namespace MantisBT\Exception;
use Exception;

abstract class ExceptionAbstract extends \Exception implements ExceptionInterface {
    protected $message = 'Unknown exception';     # Exception message
    private   $string;                            # Unknown
    protected $code    = 0;                       # User-defined exception code
    protected $file;                              # Source filename of exception
    protected $line;                              # Source line of exception
    private   $trace;                             # Unknown

	private $context = null;		# Mantis Context
    /**
     * @todo remove $context.  This is a sign something needs better design
     */
    public function __construct( $p_message = '', $p_code = 0, \Exception $p_previous = null ) {
		$t_message = var_export( $p_message, true );

		$this->context = $p_parameters;
        parent::__construct( $t_message, $p_code, $p_previous );
    }

    public function __toString() {
        return get_class($this) . " '{$this->message}' in {$this->file}({$this->line})\n"
                                . "{$this->getTraceAsString()}";
    }

	public function getContext() {
		return $this->context;
	}
}
