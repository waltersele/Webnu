@if(!empty($faqSchema['mainEntity']) && is_array($faqSchema['mainEntity']))
    <section class="mt-12" aria-labelledby="blog-faq-heading">
        <h2 id="blog-faq-heading" class="font-headline-md text-headline-md text-on-background mb-6">
            {{ __('blog.faq_heading') }}
        </h2>
        <div class="space-y-3">
            @foreach($faqSchema['mainEntity'] as $index => $item)
                @php
                    $question = $item['name'] ?? '';
                    $answer = $item['acceptedAnswer']['text'] ?? '';
                @endphp
                @if($question !== '' && $answer !== '')
                    <details class="wn-blog-glass rounded-2xl bg-surface-container-lowest group">
                        <summary class="cursor-pointer list-none p-5 font-label-lg font-semibold text-on-background flex items-center justify-between gap-3">
                            <span>{{ $question }}</span>
                            <span class="material-symbols-outlined text-text-muted group-open:rotate-180 transition-transform">expand_more</span>
                        </summary>
                        <div class="px-5 pb-5 font-body-md text-body-md text-text-muted border-t border-outline-variant pt-4">
                            {!! nl2br(e($answer)) !!}
                        </div>
                    </details>
                @endif
            @endforeach
        </div>
    </section>
@endif
