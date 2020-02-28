<?php

namespace App\Http\Controllers;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class BaseController extends Controller
{
    /**
     * 数据返回
     * @param mixed $data 数据
     * @param int $code 代码
     * @param string $message 信息
     * @return \Illuminate\Http\JsonResponse
     */
    public function success($data = [], $code = 200, $message = 'success')
    {
        return response()->json([
            'code' => $code,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * 数据返回
     * @param string $message 错误信息
     * @param int $code 错误代码
     * @param mixed $data 错误数据
     * @return \Illuminate\Http\JsonResponse
     */
    public function fail($message, $code = 500, $data = [])
    {
        return response()->json([
            'code' => $code,
            'message' => $message,
            'errors' => $data,
        ], $code);
    }

    /**
     * 抛出字段验证异常
     * @param $validator \Illuminate\Validation\Validator 验证器
     * @return \Illuminate\Http\JsonResponse
     */
    protected function errorBadRequest($validator)
    {
        $result = [];
        /* @var $validator \Illuminate\Validation\Validator */
        $messages = $validator->errors()->toArray();
        if ($messages) {
            foreach ($messages as $field => $errors) {
                foreach ($errors as $error) {
                    $result[] = [
                        'field' => $field,
                        'code' => $error,
                    ];
                }
            }
        }
        return $this->fail('出现错误了', 422, $result);
    }

    /**
     * 数组分页
     * @param array $items 分页数据
     * @param int $perPage 分页大小
     * @param array $extra 额外数据
     * @return array
     */
    public function paginate($items = [], $perPage = 10, $extra = [])
    {
        $pageStart = request()->get('page', 1);
        $offSet = ($pageStart * $perPage) - $perPage;
        $itemsForCurrentPage = collect($items)->lazy()->slice($offSet, $perPage);
        $data = new LengthAwarePaginator(
            $itemsForCurrentPage,
            count($items),
            $perPage,
            Paginator::resolveCurrentPage(),
            ['path' => Paginator::resolveCurrentPath()]
        );

        $paginated = $data->toArray();

        return [
            'items' => $paginated['data'] ?? [],
            'links' => [
                'first' => $paginated['first_page_url'] ?? '',
                'last' => $paginated['last_page_url'] ?? '',
                'prev' => $paginated['prev_page_url'] ?? '',
                'next' => $paginated['next_page_url'] ?? '',
            ],
            'meta' => array_except($paginated, [
                'data',
                'first_page_url',
                'last_page_url',
                'prev_page_url',
                'next_page_url',
            ]),
            'extra' => $extra
        ];
    }
}
