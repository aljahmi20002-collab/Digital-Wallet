<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\GiftCard;
use App\Models\GiftCardTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class GiftCardTemplateController extends Controller
{
    public function index(Request $request): View
    {
        $templates = GiftCardTemplate::query()
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%'.$request->input('q').'%'))
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->input('category')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->orderBy('sort_order')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total'    => GiftCardTemplate::count(),
            'active'   => GiftCardTemplate::where('status', 'active')->count(),
            'drafts'   => GiftCardTemplate::where('status', 'draft')->count(),
            'sent_mtd' => GiftCard::whereMonth('created_at', now()->month)->count(),
        ];

        return view('backend.gift-card-templates.index', compact('templates', 'stats'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data          = $this->validateData($request);
        $data['image'] = $this->handleImage($request, null);

        GiftCardTemplate::create($data);

        notifyEvs('success', __('Template created.'));

        return redirect()->route('admin.gift-card-templates.index');
    }

    public function update(Request $request, GiftCardTemplate $template): RedirectResponse
    {
        $data          = $this->validateData($request, $template->id);
        $data['image'] = $this->handleImage($request, $template);

        $template->update($data);

        notifyEvs('success', __('Template updated.'));

        return redirect()->route('admin.gift-card-templates.index');
    }

    public function destroy(GiftCardTemplate $template): RedirectResponse
    {
        if ($template->image && Storage::disk('public')->exists($template->image)) {
            Storage::disk('public')->delete($template->image);
        }

        $template->delete();

        notifyEvs('success', __('Template deleted.'));

        return back();
    }

    /**
     * Persist a new sort order coming from the drag-and-drop list.
     * Matches the contract of WalletEarnPlanController::positionUpdate so
     * the shared front-end pattern (sortable.js + jQuery POST) works
     * without any custom glue.
     */
    public function positionUpdate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'positions'         => ['required', 'array'],
            'positions.*.id'    => ['required', 'integer', 'exists:gift_card_templates,id'],
            'positions.*.order' => ['required', 'integer', 'min:1'],
        ]);

        foreach ((array) $validated['positions'] as $item) {
            GiftCardTemplate::query()
                ->where('id', (int) $item['id'])
                ->update(['sort_order' => (int) $item['order']]);
        }

        return response()->json([
            'status'  => 'success',
            'message' => __('Template order updated successfully.'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateData(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name'             => ['required', 'string', 'max:120'],
            'category'         => ['required', 'string', 'max:60'],
            'preset_key'       => ['required', 'in:'.implode(',', GiftCardTemplate::PRESETS)],
            'background_color' => ['nullable', 'string', 'max:32'],
            'text_color'       => ['nullable', 'string', 'max:32'],
            'ribbon_text'      => ['nullable', 'string', 'max:60'],
            'default_amount'   => ['nullable', 'numeric', 'min:1', 'max:99999.99'],
            'status'           => ['required', 'in:active,draft,inactive'],
            'sort_order'       => ['nullable', 'integer'],
            'image'            => ['nullable', 'image', 'max:2048'],
        ]);
    }

    private function handleImage(Request $request, ?GiftCardTemplate $template): ?string
    {
        if (! $request->hasFile('image')) {
            return $template?->image;
        }

        if ($template && $template->image && Storage::disk('public')->exists($template->image)) {
            Storage::disk('public')->delete($template->image);
        }

        return $request->file('image')->store('gift-cards/templates', 'public');
    }
}
