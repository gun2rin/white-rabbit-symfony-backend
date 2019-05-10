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

namespace Rabbit\Controller;

use Rabbit\Exception\FileTransformException;
use Rabbit\Service\FileTransformInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;


/**
 * Class ApiController
 * @package Rabbit\Controller
 * @author Vladimir Ganturin <gun2rin@gmail.com>
 */
class ApiController extends Controller
{

    /**
     * Necessary CORS headers like Access-Control-Allow-Origin
     * are provided by NelmioCorsBundle.
     * So there's no need to set up these headers
     * in controller for Response object.
     *
     * @see ../config/packages/nelmio_cors.yaml
     * @see https://github.com/nelmio/NelmioCorsBundle
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Rabbit\Service\FileTransformInterface $fileHandler
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function transformImage(Request $request, FileTransformInterface $fileHandler): JsonResponse
    {

        $response = new JsonResponse();

        $result = null;

        $file = $request->files->get('file');


        $options = [
            'background' => $request->get('background'),
            'foreground' => $request->get('foreground')
         ];


        try {

            $result = $fileHandler->setFile($file,$options)->validate()->transform();

        } catch (FileTransformException $e) {

            $response->setStatusCode(400);

        } catch (\Exception $e) {

            $response->setStatusCode(409);

        }


        $msg = [
            'success' => !$fileHandler->getError(),
            'thumb' => $result['thumb'],
            'fullImage' => $result['image'],
            'fileSize' => $result['fileSize'],
            'msg' => $fileHandler->getMessages(),
        ];


        $response->setContent(json_encode($msg));

        return $response;
    }


}
