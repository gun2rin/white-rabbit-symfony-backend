<?php
/**
 *
 * This file is part of the White Rabbit application.
 *
 * (c) Vladimir Ganturin <gun2rin@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
namespace Rabbit\Service;

/**
 * Interface FileTransformInterface
 * @package Rabbit\Service
 * @author Vladimir Ganturin <gun2rin@gmail.com>
 */
interface FileTransformInterface
{

    /**
     * Defines the uploaded file to further work
     * @param \SplFileInfo $file
     * @param array $options
     * @return mixed
     */
    public function setFile(\SplFileInfo $file, array $options);


    /**
     * Validates the uploaded file
     * @return mixed
     *
     */
    public function validate();


    /**
     * Main job.
     * Working with file in any way.
     * @return mixed
     */
    public function transform();


    /**
     * Error flag
     * @return boolean
     */
    public function getError():bool;


    /**
     * Some array of strings
     * for example validation error messages
     * @return array
     */
    public function getMessages():array;

}