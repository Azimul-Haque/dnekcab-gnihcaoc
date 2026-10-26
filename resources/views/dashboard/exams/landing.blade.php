@extends('layouts.app')
@section('title') ড্যাশবোর্ড | পরীক্ষাসমূহ @endsection

@section('content')

@section('page-header') পরীক্ষার বিভাগসমূহ @endsection

<div class="container-fluid">
    <div class="row">
        @php
            // প্রফেশনাল কালার ক্লাসের একটি অ্যারে
            $colors = ['bg-info', 'bg-success', 'bg-warning', 'bg-danger', 'bg-primary', 'bg-teal', 'bg-purple'];
        @endphp

        @foreach($examcategories as $index => $category)
            @php
                $currentColor = $colors[$index % count($colors)];
            @endphp

            <div class="col-lg-3 col-6 mb-4">
                <div class="small-box {{ $currentColor }} shadow border-0 position-relative" style="border-radius: 12px; overflow: hidden; transition: transform .3s ease;">
                    
                    {{-- ভাসমান এডিট বাটন (Top-Right Edit Button) --}}
                    <button type="button" 
                            class="btn btn-sm btn-light shadow-sm rounded-circle edit-category-btn" 
                            data-id="{{ $category->id }}"
                            data-name="{{ $category->name }}"
                            data-thumbnail="{{ $category->thumbnail ?? '📚' }}"
                            data-update-url="{{ route('dashboard.exams.category.update', ['id' => $category->id]) }}"
                            title="ক্যাটাগরি এডিট করুন"
                            style="width: 32px; height: 32px; padding: 0; line-height: 32px; opacity: 0.9; transition: all 0.2s;">
                        <i class="fas fa-pen text-dark" style="font-size: 0.85rem;"></i>
                    </button>

                    {{-- ইনফো সেকশন (ইমোজি থাম্বনেইল সহ) --}}
                    <div class="inner p-3">
                        <div class="d-flex align-items-center mb-1">
                            <span class="mr-2" style="font-size: 2.2rem; line-height: 1;">{{ $category->thumbnail ?? '📚' }}</span>
                            <h3 class="mb-0" style="font-weight: 700;">{{ $category->exams_count ?? $category->exams->count() }}</h3>
                        </div>
                        <p class="font-weight-bold mb-0 text-truncate" style="font-size: 1.15rem; opacity: 0.95;" title="{{ $category->name }}">
                            {{ $category->name }}
                        </p>
                    </div>
                    
                    {{-- ওয়াটারমার্ক ব্যাকগ্রাউন্ড আইকন --}}
                    <div class="icon" style="top: -5px; right: 12px; font-size: 60px; opacity: 0.25;">
                        <span>{{ $category->thumbnail ?? '📚' }}</span>
                    </div>
                    
                    <a href="{{ route('dashboard.exams', ['id' => $category->id]) }}" class="small-box-footer py-2" style="background: rgba(0,0,0,0.12); border-top: 1px solid rgba(255,255,255,0.15); font-weight: 500;">
                        পরীক্ষাসমূহ দেখুন <i class="fas fa-arrow-circle-right ml-1"></i>
                    </a>
                </div>
            </div>
        @endforeach

        {{-- নতুন ক্যাটাগরি যোগ করার কার্ড --}}
        <div class="col-lg-3 col-6 mb-4">
            <div class="small-box shadow-sm border-dashed" 
                 style="background-color: #ffffff; border: 2px dashed #00695C; border-radius: 12px; cursor: pointer; transition: transform .2s;"
                 data-toggle="modal" data-target="#addTopicModal">
                <div class="inner text-center" style="padding: 28px 10px;">
                    <i class="fas fa-plus-circle fa-2x mb-2" style="color: #00695C;"></i>
                    <p class="font-weight-bold text-dark mb-0" style="font-size: 1.05rem;">নতুন ক্যাটাগরি যোগ করুন</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ১. নতুন ক্যাটাগরি যোগ করার মডাল --}}
<div class="modal fade" id="addTopicModal" tabindex="-1" role="dialog" aria-labelledby="addTopicModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
        <div class="modal-header text-white" style="background-color: #00695C; border-top-left-radius: 16px; border-top-right-radius: 16px;">
          <h5 class="modal-title font-weight-bold" id="addTopicModalLabel"><i class="fas fa-folder-plus mr-2"></i>নতুন ক্যাটাগরি যোগ</h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form method="post" action="{{ route('dashboard.exams.category.store') }}">
            @csrf
            <div class="modal-body p-4">
                  <div class="form-group mb-3">
                      <label class="font-weight-bold text-secondary">ক্যাটাগরির নাম</label>
                      <div class="input-group">
                          <input type="text" name="name" class="form-control form-control-lg" value="{{ old('name') }}" placeholder="যেমন: বিসিএস প্রিলিমিনারি" required style="border-radius: 10px 0 0 10px;">
                          <div class="input-group-append">
                              <span class="input-group-text bg-light" style="border-radius: 0 10px 10px 0;"><i class="far fa-bookmark text-teal"></i></span>
                          </div>
                      </div>
                  </div>

                  <div class="form-group mb-2">
                      <label class="font-weight-bold text-secondary">থাম্বনেইল (ইউনিকোড ইমোজি)</label>
                      <div class="input-group">
                          <input type="text" name="thumbnail" id="add_thumbnail_input" class="form-control form-control-lg text-center font-weight-bold" value="{{ old('thumbnail', '📚') }}" required style="font-size: 1.5rem; border-radius: 10px 0 0 10px;">
                          <div class="input-group-append">
                              <span class="input-group-text bg-light" style="border-radius: 0 10px 10px 0;"><i class="far fa-image text-teal"></i></span>
                          </div>
                      </div>
                  </div>

                  {{-- পপুলার ইমোজি নির্বাচন করার প্যালেট --}}
                  <label class="small text-muted font-weight-bold mt-2">নিচের যে কোনো একটি ইমোজিতে ক্লিক করুন:</label>
                  <div class="emoji-picker-grid p-2 border rounded bg-light d-flex flex-wrap justify-content-center" style="gap: 8px; max-height: 130px; overflow-y: auto;">
                      @php
                          $emojis = ['📚', '🔬', '📖', '📊', '🎓', '📝', '🏫', '🧪', '💻', '💡', '🎨', '📑', '🎯', '🏆', '⚖️', '📌', '⚡', '🧠', '📐', '📂'];
                      @endphp
                      @foreach($emojis as $emoji)
                          <span class="emoji-option p-2 rounded cursor-pointer" onclick="selectEmoji('add_thumbnail_input', '{{ $emoji }}')" style="font-size: 1.6rem; cursor: pointer; transition: transform 0.1s;">{{ $emoji }}</span>
                      @endforeach
                  </div>
            </div>
            <div class="modal-footer bg-light" style="border-bottom-left-radius: 16px; border-bottom-right-radius: 16px;">
              <button type="button" class="btn btn-outline-secondary px-4" data-dismiss="modal">ফিরে যান</button>
              <button type="submit" class="btn text-white px-4" style="background-color: #00695C;">সেভ করুন</button>
            </div>
        </form>
      </div>
    </div>
</div>

{{-- ২. ক্যাটাগরি এডিট করার মডাল --}}
<div class="modal fade" id="editCategoryModal" tabindex="-1" role="dialog" aria-labelledby="editCategoryModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
        <div class="modal-header text-white" style="background-color: #0F4C5C; border-top-left-radius: 16px; border-top-right-radius: 16px;">
          <h5 class="modal-title font-weight-bold" id="editCategoryModalLabel"><i class="fas fa-edit mr-2"></i>ক্যাটাগরি সংশোধন করুন</h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form method="post" id="editCategoryForm" action="">
            @csrf
            @method('PUT')
            <input type="hidden" name="id" id="edit_category_id">

            <div class="modal-body p-4">
                  <div class="form-group mb-3">
                      <label class="font-weight-bold text-secondary">ক্যাটাগরির নাম</label>
                      <div class="input-group">
                          <input type="text" name="name" id="edit_category_name" class="form-control form-control-lg" placeholder="ক্যাটাগরির নাম" required style="border-radius: 10px 0 0 10px;">
                          <div class="input-group-append">
                              <span class="input-group-text bg-light" style="border-radius: 0 10px 10px 0;"><i class="far fa-bookmark text-info"></i></span>
                          </div>
                      </div>
                  </div>

                  <div class="form-group mb-2">
                      <label class="font-weight-bold text-secondary">থাম্বনেইল (ইউনিকোড ইমোজি)</label>
                      <div class="input-group">
                          <input type="text" name="thumbnail" id="edit_thumbnail_input" class="form-control form-control-lg text-center font-weight-bold" required style="font-size: 1.5rem; border-radius: 10px 0 0 10px;">
                          <div class="input-group-append">
                              <span class="input-group-text bg-light" style="border-radius: 0 10px 10px 0;"><i class="far fa-image text-info"></i></span>
                          </div>
                      </div>
                  </div>

                  {{-- ইমোজি সিলেক্টর প্যালেট --}}
                  <label class="small text-muted font-weight-bold mt-2">ইমোজি পরিবর্তন করতে সিলেক্ট করুন:</label>
                  <div class="emoji-picker-grid p-2 border rounded bg-light d-flex flex-wrap justify-content-center" style="gap: 8px; max-height: 130px; overflow-y: auto;">
                      @foreach($emojis as $emoji)
                          <span class="emoji-option p-2 rounded cursor-pointer" onclick="selectEmoji('edit_thumbnail_input', '{{ $emoji }}')" style="font-size: 1.6rem; cursor: pointer; transition: transform 0.1s;">{{ $emoji }}</span>
                      @endforeach
                  </div>
            </div>
            <div class="modal-footer bg-light" style="border-bottom-left-radius: 16px; border-bottom-right-radius: 16px;">
              <button type="button" class="btn btn-outline-secondary px-4" data-dismiss="modal">বাতিল</button>
              <button type="submit" class="btn text-white px-4" style="background-color: #0F4C5C;">হালনাগাদ করুন</button>
            </div>
        </form>
      </div>
    </div>
</div>

@endsection

@section('third_party_scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script>
    // ইমোজি নির্বাচন ফাংশন
    function selectEmoji(inputId, emoji) {
        $('#' + inputId).val(emoji);
    }

    // এডিট বাটনে ক্লিক করলে মডালে ডাটা পপুলেট করা
    $(document).on('click', '.edit-category-btn', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        var thumbnail = $(this).data('thumbnail');

        $('#edit_category_id').val(id);
        $('#edit_category_name').val(name);
        $('#edit_thumbnail_input').val(thumbnail);

        // ল্যারাভেল রুটে ডাইনামিকালি ID সেট করা
        var updateUrl = "{{ route('dashboard.exams.category.update', ['id' => ':id']) }}";
        updateUrl = updateUrl.replace(':id', id);
        $('#editCategoryForm').attr('action', updateUrl);

        $('#editCategoryModal').modal('show');
    });

    // সার্চ ফিল্টার
    $(document).on('click', '#search-button', function() {
      if($('#search-param').val() != '') {
        var urltocall = '{{ route('dashboard.exams') }}' +  '/' + $('#search-param').val().replace(/\\|\//g, '%');
        location.href= urltocall;
      } else {
        $('#search-param').css({ "border": '#FF0000 2px solid'});
        Swal.fire({
            icon: 'warning',
            title: 'কিছু লিখে খুঁজুন!'
        })
      }
    });

    $("#search-param").keyup(function(e) {
      if(e.which == 13) {
        $("#search-button").click();
      }
    });
</script>

<style>
    .small-box:hover {
        transform: translateY(-4px);
        box-shadow: 0 .5rem 1.2rem rgba(0,0,0,.18)!important;
    }
    .edit-category-btn:hover {
        transform: scale(1.15);
        background-color: #ffffff !important;
    }
    .emoji-option:hover {
        background-color: #e0f2f1;
        transform: scale(1.25);
    }
    .border-dashed {
        border-style: dashed !important;
    }
</style>
@endsection