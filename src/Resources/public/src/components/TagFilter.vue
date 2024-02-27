<template>
    <form action="">
        <div class="flex flex-row space-x-2">
            <select name="tags" id="tags" class="border border-gray-300 rounded-md" v-model="selectedTag" @change="filterTags()">
                <option value="">All</option>
                <option v-for="tag in tags" :value="tag.name">${tag.name}</option>
            </select>
        </div>
    </form>
</template>

<script setup>
import {defineProps, ref} from "vue"

const tags = ref([])
const selectedTag = ref("")

const queryParams = new URLSearchParams(window.location.search)
if( queryParams.get('tag') ){
    selectedTag.value = queryParams.get('tag')
}

// get all tags as key => Value string
const props = defineProps({
    tags: {
        type: String,
        required: true
    }
})

tags.value = JSON.parse(props.tags)

const filterTags = () => {
    if( selectedTag.value === '' ) {
        window.location.href = '/admin/n8n-manager?tag='
    }else{
        window.location.href = `/admin/n8n-manager?tag=${selectedTag.value}`
    }
}

</script>
