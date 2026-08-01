{{-- Front and back are attached independently: one tile, one file, one button
     each. No capture attribute, so the phone offers camera or gallery. --}}
<div class="id-block">
    <span class="id-block__title">ID Card — both sides required</span>
    <div class="id-tiles">
        <div class="id-tile" id="id-tile-front">
            <span class="id-tile__label">Front of ID</span>
            <img class="id-tile__thumb" id="id_front_thumb" alt="Front of ID preview">
            <span class="id-tile__doc" id="id_front_doc">📄</span>
            <span class="id-tile__state" id="id_front_state">Not attached yet</span>
            <label class="btn btn-outline" for="id_card_front" id="id_front_button">Add front</label>
            <input class="hidden-input" type="file" name="id_card_front" id="id_card_front" accept="image/*,.pdf,application/pdf">
        </div>
        <div class="id-tile" id="id-tile-back">
            <span class="id-tile__label">Back of ID</span>
            <img class="id-tile__thumb" id="id_back_thumb" alt="Back of ID preview">
            <span class="id-tile__doc" id="id_back_doc">📄</span>
            <span class="id-tile__state" id="id_back_state">Not attached yet</span>
            <label class="btn btn-outline" for="id_card_back" id="id_back_button">Add back</label>
            <input class="hidden-input" type="file" name="id_card_back" id="id_card_back" accept="image/*,.pdf,application/pdf">
        </div>
    </div>
    <p class="id-hint">Take a photo or pick a file for each side. Accepted: JPG, PNG or PDF.</p>
</div>
