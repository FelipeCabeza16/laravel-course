<x-profile :sharedData="$sharedData" docTitle="Seguidos {{$sharedData['username']}}">
  @include('profile-following-only')
</x-profile>