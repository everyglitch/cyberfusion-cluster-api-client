<?php

namespace Vdhicts\Cyberfusion\ClusterApi\Endpoints;

use Vdhicts\Cyberfusion\ClusterApi\Exceptions\RequestException;
use Vdhicts\Cyberfusion\ClusterApi\Models\HtpasswdUser;
use Vdhicts\Cyberfusion\ClusterApi\Request;
use Vdhicts\Cyberfusion\ClusterApi\Response;
use Vdhicts\Cyberfusion\ClusterApi\Support\ListFilter;

class HtpasswdUsers extends Endpoint
{
    /**
     * @param ListFilter|null $filter
     * @return Response
     * @throws RequestException
     */
    public function list(ListFilter $filter = null): Response
    {
        if (is_null($filter)) {
            $filter = new ListFilter();
        }

        $request = (new Request())
            ->setMethod(Request::METHOD_GET)
            ->setUrl(sprintf('htpasswd-users?%s', $filter->toQuery()));

        $response = $this
            ->client
            ->request($request);
        if (!$response->isSuccess()) {
            return $response;
        }

        return $response->setData([
            'htpasswdUsers' => array_map(
                function (array $data) {
                    return (new HtpasswdUser())->fromArray($data);
                },
                $response->getData()
            ),
        ]);
    }

    /**
     * @param int $id
     * @return Response
     * @throws RequestException
     */
    public function get(int $id): Response
    {
        $request = (new Request())
            ->setMethod(Request::METHOD_GET)
            ->setUrl(sprintf('htpasswd-users/%d', $id));

        $response = $this
            ->client
            ->request($request);
        if (!$response->isSuccess()) {
            return $response;
        }

        return $response->setData([
            'htpasswdUser' => (new HtpasswdUser())->fromArray($response->getData()),
        ]);
    }

    /**
     * @param HtpasswdUser $htpasswdUser
     * @return Response
     * @throws RequestException
     */
    public function create(HtpasswdUser $htpasswdUser): Response
    {
        $this->validateRequired($htpasswdUser, 'create', [
            'username',
            'password',
            'htpasswd_file_id',
        ]);

        $request = (new Request())
            ->setMethod(Request::METHOD_POST)
            ->setUrl('htpasswd-users')
            ->setBody($this->filterFields($htpasswdUser->toArray(), [
                'username',
                'password',
                'htpasswd_file_id',
            ]));

        $response = $this
            ->client
            ->request($request);
        if (!$response->isSuccess()) {
            return $response;
        }

        $htpasswdUser = (new HtpasswdUser())->fromArray($response->getData());

        // Log which cluster is affected by this change
        $this
            ->client
            ->addAffectedCluster($htpasswdUser->getClusterId());

        return $response->setData([
            'htpasswdUser' => $htpasswdUser,
        ]);
    }

    /**
     * @param HtpasswdUser $htpasswdUser
     * @return Response
     * @throws RequestException
     */
    public function update(HtpasswdUser $htpasswdUser): Response
    {
        $this->validateRequired($htpasswdUser, 'update', [
            'username',
            'password',
            'htpasswd_file_id',
            'id',
            'cluster_id',
        ]);

        $request = (new Request())
            ->setMethod(Request::METHOD_PUT)
            ->setUrl(sprintf('htpasswd-users/%d', $htpasswdUser->getId()))
            ->setBody($this->filterFields($htpasswdUser->toArray(), [
                'username',
                'password',
                'htpasswd_file_id',
                'id',
                'cluster_id',
            ]));

        $response = $this
            ->client
            ->request($request);
        if (!$response->isSuccess()) {
            return $response;
        }

        $htpasswdUser = (new HtpasswdUser())->fromArray($response->getData());

        // Log which cluster is affected by this change
        $this
            ->client
            ->addAffectedCluster($htpasswdUser->getClusterId());

        return $response->setData([
            'htpasswdUser' => $htpasswdUser,
        ]);
    }

    /**
     * @param int $id
     * @return Response
     * @throws RequestException
     */
    public function delete(int $id): Response
    {
        // Log the affected cluster by retrieving the model first
        $result = $this->get($id);
        if ($result->isSuccess()) {
            $clusterId = $result
                ->getData('htpasswdUser')
                ->getClusterId();

            $this
                ->client
                ->addAffectedCluster($clusterId);
        }

        $request = (new Request())
            ->setMethod(Request::METHOD_DELETE)
            ->setUrl(sprintf('htpasswd-users/%d', $id));

        return $this
            ->client
            ->request($request);
    }
}
