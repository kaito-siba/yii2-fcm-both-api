<?php

namespace aksafan\fcm\source\responses\apiV1;

use aksafan\fcm\source\helpers\ErrorsHelper;
use aksafan\fcm\source\responses\AbstractResponse;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ApiV1AbstractResponse.
 */
abstract class ApiV1AbstractResponse extends AbstractResponse
{
    const RESULTS = 'results';

    /**
     * Check if the response given by fcm is parsable.
     *
     * @param ResponseInterface $response
     *
     * @return bool
     */
    public function validateResponse($responseObject): bool
    {
        if (null === $responseObject) {
            return false;
        }

        if ($responseObject['state'] === 'fulfilled') {
            /** @var ResponseInterface $response */
            $response = $responseObject['value'];
            $statusCode = $response->getStatusCode();
            $contents = $response->getBody()->getContents();
        } else {
            /** @var \GuzzleHttp\Exception\TransferException $exception */
            $exception = $responseObject['reason'];
            $statusCode = $exception->getCode();
            $contents = $exception->getMessage();
        }

        if (200 === ($statusCode)) {
            return true;
        }

        if (404 === $statusCode) {
            if ($contents === 'Requested entity was not found.') {
                return true;
            }
        }

        if (400 === $statusCode) {
            \Yii::error(ErrorsHelper::getStatusCodeErrorMessage($statusCode, $contents, $this), ErrorsHelper::GUZZLE_HTTP_CLIENT_ERROR);
            \Yii::error('Something in the request data was wrong: check if all data{...}values are converted to strings.', ErrorsHelper::GUZZLE_HTTP_CLIENT_ERROR);
            $this->setErrorStatusDescription(ErrorsHelper::STATUS_CODE_400, $contents);

            return false;
        }

        if (403 === $statusCode) {
            \Yii::error(ErrorsHelper::getStatusCodeErrorMessage($statusCode, self::UNAUTHORIZED_REQUEST_EXCEPTION_MESSAGE, $this), ErrorsHelper::GUZZLE_HTTP_CLIENT_ERROR);
            \Yii::error('To use the new FCM HTTP v1 API, you need to enable FCM API on your Google API dashboard first - https://console.developers.google.com/apis/library/fcm.googleapis.com/.', ErrorsHelper::GUZZLE_HTTP_CLIENT_ERROR);
            $this->setErrorStatusDescription(ErrorsHelper::STATUS_CODE_403, $contents);

            return false;
        }

        \Yii::error(ErrorsHelper::getStatusCodeErrorMessage($statusCode, $contents, $this), ErrorsHelper::GUZZLE_HTTP_CLIENT_OTHER_ERRORS);
        $this->setErrorStatusDescription(ErrorsHelper::OTHER_STATUS_CODES, $contents);

        return false;
    }


    /**
     * Handles response.
     *
     * @param ResponseInterface|null $responseObject
     *
     * @return AbstractResponse
     */
    public function handleResponse($responseObject = null): AbstractResponse
    {
        if ($this->validateResponse($responseObject)) {
            $this->parseResponse($this->getResponseBody($responseObject['value']));
        }

        return $this;
    }
}
