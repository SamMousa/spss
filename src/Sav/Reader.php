<?php

namespace SPSS\Sav;

use SPSS\Buffer;
use SPSS\Exception;


class Reader
{
    /**
     * @var Record\Header
     */
    public $header;

    /**
     * @var Record\Variable[]
     */
    public $variables = [];

    /**
     * @var Record\ValueLabel[]
     */
    public $valueLabels = [];

    /**
     * @var array
     */
    public $documents = [];

    /**
     * @var Record\Info[]
     */
    public $info = [];

    /**
     * @var array Data matrix
     */
    public $data = [];


    /**
     * Initialize the reader.
     *
     * @param Buffer $buffer
     * @throws Exception
     */
    private function __construct( Buffer $buffer )
    {
        $buffer->context = $this;
        $this->header = Record\Header::fill( $buffer );

        do {
            $recType = $buffer->readInt();
            switch ( $recType )
            {
                case Record\Variable::TYPE:
                    $this->variables[] = Record\Variable::fill( $buffer );
                    break;

                case Record\ValueLabel::TYPE:
                    $this->valueLabels[] = Record\ValueLabel::fill( $buffer );
                    break;

                case Record\Document::TYPE:
                    $this->documents = Record\Document::fill( $buffer )->lines;
                    break;

                case Record\Info::TYPE:
                    $subtype = $buffer->readInt();
                    switch ( $subtype )
                    {
                        case Record\Info\MachineInteger::SUBTYPE:
                            $this->info[$subtype] = Record\Info\MachineInteger::fill( $buffer );
                            break;

                        case Record\Info\MachineFloatingPoint::SUBTYPE:
                            $this->info[$subtype] = Record\Info\MachineFloatingPoint::fill( $buffer );
                            break;

                        case Record\Info\VariableDisplayParam::SUBTYPE:
                            $this->info[$subtype] = Record\Info\VariableDisplayParam::fill( $buffer );
                            break;

                        case Record\Info\LongVariableNames::SUBTYPE:
                            $this->info[$subtype] = Record\Info\LongVariableNames::fill( $buffer );
                            break;

                        case Record\Info\VeryLongString::SUBTYPE:
                            $this->info[$subtype] = Record\Info\VeryLongString::fill( $buffer );
                            break;

                        case Record\Info\ExtendedNumberOfCases::SUBTYPE:
                            $this->info[$subtype] = Record\Info\ExtendedNumberOfCases::fill( $buffer );
                            break;

                        case Record\Info\DataFileAttributes::SUBTYPE:
                            $this->info[$subtype] = Record\Info\DataFileAttributes::fill( $buffer );
                            break;

                        case Record\Info\VariableAttributes::SUBTYPE:
                            $this->info[$subtype] = Record\Info\VariableAttributes::fill( $buffer );
                            break;

                        case Record\Info\CharacterEncoding::SUBTYPE:
                            $this->info[$subtype] = Record\Info\CharacterEncoding::fill( $buffer );
                            break;

                        case Record\Info\LongStringValueLabels::SUBTYPE:
                            $this->info[$subtype] = Record\Info\LongStringValueLabels::fill( $buffer );
                            break;

                        case Record\Info\LongStringMissingValues::SUBTYPE:
                            $this->info[$subtype] = Record\Info\LongStringMissingValues::fill( $buffer );
                            break;

                        default:
                            $this->info[$subtype] = Record\Info\Unknown::fill( $buffer );
                    }
                    break;
            }
        }
        while ( $recType != Record\Data::TYPE );

        $this->data = Record\Data::fill( $buffer )->matrix;
    }


    /**
     * Reads sav file from file.
     *
     * @param string $file Location to existing file
     *
     * @return Reader Reader interface
     */
    public static function fromFile( $file )
    {
        return new self( Buffer::factory( fopen( $file, 'r' ) ) );
    }


    /**
     * Returns the reader from given SPSS file string.
     *
     * @param string $str Contents of the SPSS file
     *
     * @return Reader
     */
    public static function fromString( $str )
    {
        return new self( Buffer::factory( $str ) );
    }

}