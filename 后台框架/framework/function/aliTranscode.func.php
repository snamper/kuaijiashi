<?php

defined('IN_IA') or exit('Access Denied');
require_once IA_ROOT . '/framework/library/alioss/aliyun-php-sdk-core/Config.php';
use Mts\Request\V20140618 as Mts;

function aliTranscode($filename)
{
    global $_W;
    $region          = 'cn-shenzhen';
    $accessKeyId     = $_W['setting']['remote']['alioss']['key'];
    $accessKeySecret = $_W['setting']['remote']['alioss']['secret'];
    $pipelineId      = $_W['setting']['remote']['alioss']['pipelineId'];
#oss-cn-hangzhou、oss-cn-shanghai、oss-us-west-1等;与region对应
    require_once IA_ROOT . '/framework/library/alioss/autoload.php';
    load()->model('attachment');
    $buckets             = attachment_alioss_buctkets($_W['setting']['remote']['alioss']['key'], $_W['setting']['remote']['alioss']['secret']);
    $ossLocation         = $buckets[$_W['setting']['remote']['alioss']['bucket']]['location'];
    $inputObject         = $filename;
    $inputBucket         = $_W['setting']['remote']['alioss']['bucket'];
    $filename            = substr($filename, 0, strrpos($filename, '.'));
    $outputObject        = $filename . '.mp3';
    $outputBucket        = $_W['setting']['remote']['alioss']['outBucket'];
    $transcodeTemplateId = $_W['setting']['remote']['alioss']['transcodeTemplateId'];
    $profile             = DefaultProfile::getProfile($region, $accessKeyId, $accessKeySecret);
    $client              = new DefaultAcsClient($profile);
    $inputFile           = array(
        'Location' => $ossLocation,
        'Bucket'   => $inputBucket,
        'Object'   => urlencode($inputObject));
    $outputs   = array();
    $outputs[] = array(
        'OutputObject' => urlencode($outputObject),
        'TemplateId'   => $transcodeTemplateId,
    );
    $request = new Mts\SubmitJobsRequest();
    $request->setAcceptFormat('JSON');
    $request->setInput(json_encode($inputFile));
    $request->setOutputBucket($outputBucket);
    $request->setOutputLocation($ossLocation);
    $request->setOUtputs(json_encode($outputs));
    $request->setPipelineId($pipelineId);
    $response = $client->getAcsResponse($request);

    $jobId = $response->{'JobResultList'}->{'JobResult'}[0]->{'Job'}->{'JobId'};

    if (!empty($jobId)) {
        $state = 0;
        do {
            $result = checkTranscodeStatus($jobId, $client);
            if ($result['state'] == 'TranscodeFail') {
                return error(1, "转码失败");
            }
            if ($result['state'] == 'TranscodeSuccess') {
                $state = 1;
                return error(0, $result['url']);

            }
        } while (!$state);
    } else {
        return error(1, '转码错误');
    }

}

function checkTranscodeStatus($jobId, $client)
{
    $return  = [];
    $request = new Mts\QueryJobListRequest();
    $request->setAcceptFormat('JSON');
    $request->setJobIds($jobId);
    $response = $client->getAcsResponse($request);
    $jobs     = $response->JobList->Job;

    $job             = $jobs[0];
    $return['state'] = $job->State;
    $return['url']   = urldecode($job->Output->OutputFile->Object);
    return $return;
}
