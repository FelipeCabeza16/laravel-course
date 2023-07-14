<x-profile :sharedData="$sharedData" docTitle="Perfil de {{$sharedData['username']}}">
  @include('profile-posts-only')
</x-profile>