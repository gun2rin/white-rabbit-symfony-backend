<?php

namespace Rabbit\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\RedirectResponse;
/**
 * Class DownloadController
 * @package Rabbit\Controller
 * @author Vladimir Ganturin <gun2rin@gmail.com>
 */
class DownloadController extends Controller
{

    /**
     * @param string $filename
     * @param array $params
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|RedirectResponse
     */
    public function download(string $filename, array $params)
    {

        $filePath = $params['work_dir'].'/'.$filename;

        try
        {
            $response = new BinaryFileResponse($filePath);
            $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT);
        }
        catch (\Exception $e)
        {

            $response = new RedirectResponse( getenv('CORS_ALLOW_ORIGIN'));

        }


        return $response;

    }
}
