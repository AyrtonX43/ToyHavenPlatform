<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && (auth()->user()->isSeller() || auth()->user()->isAdmin());
    }

    /**
     * Prepare the data for validation (trim video_url so pasted links validate).
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('video_url') && is_string($this->video_url)) {
            $this->merge(['video_url' => trim($this->video_url) ?: null]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'categories' => 'required|array|min:1',
            'categories.*' => 'exists:categories,id',
            'brand' => 'nullable|string|max:100',
            'sku' => 'required|string|max:100|unique:products,sku,' . ($this->route('id') ?? ''),
            'price' => 'required|numeric|min:0',
            'base_price' => 'nullable|numeric|min:0',
            'amazon_reference_price' => 'nullable|numeric|min:0',
            'amazon_reference_image' => 'nullable|string|max:2000',
            'amazon_reference_url' => 'nullable|url|max:2000',
            'platform_fee_percentage' => 'nullable|numeric|min:0|max:100',
            'tax_percentage' => 'nullable|numeric|min:0|max:100',
            'final_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpeg,jpg,png|max:2048',
            'imported_image_urls' => 'nullable|string',
            'imported_video_urls' => 'nullable|string',
            'images_to_delete' => 'nullable|array',
            'images_to_delete.*' => 'integer',
            'video_url' => 'nullable|url|max:500',
            'video_file' => 'nullable|file|mimes:mp4,mov,avi,wmv,flv,webm|max:51200', // 50MB max
            'variations_json' => 'nullable|string',
            'weight' => 'nullable|numeric|min:0',
            'length' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
        ];
    }
    
    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // For updates, check if product already has images
            $isUpdate = $this->route('id') !== null;
            
            if ($isUpdate) {
                // On update, check if product has existing images
                $productId = $this->route('id');
                if ($productId) {
                    $product = \App\Models\Product::find($productId);
                    if ($product && $product->images && $product->images->count() > 0) {
                        // Product has images, so new images are optional
                        return;
                    }
                }
            }
            
            if ($isUpdate) {
                // On update, images are optional if product already has images
                return;
            }
            
            // For create, ensure at least one image source is provided
            $hasFileImages = $this->hasFile('images') && count($this->file('images')) > 0;
            $hasImportedImages = !empty($this->input('imported_image_urls'));
            
            if (!$hasFileImages && !$hasImportedImages) {
                $validator->errors()->add('images', 'Please upload at least one image or import images from Amazon reference.');
            }
        });
    }
}
