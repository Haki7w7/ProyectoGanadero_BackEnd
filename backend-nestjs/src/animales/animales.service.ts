
import { Injectable, NotFoundException } from '@nestjs/common';
import { InjectRepository } from '@nestjs/typeorm';
import { Repository } from 'typeorm';
import { Animal } from './entities/animal.entity';
import { CreateAnimalDto } from './dto/create-animal.dto';
import { UpdateAnimalDto } from './dto/update-animal.dto';

@Injectable()
export class AnimalesService {
  constructor(
    @InjectRepository(Animal)
    private readonly animalRepository: Repository<Animal>,
  ) {}

  async create(createAnimalDto: CreateAnimalDto): Promise<Animal> {
    const nuevoAnimal = this.animalRepository.create(createAnimalDto);
    return await this.animalRepository.save(nuevoAnimal);
  }

  async findAll(): Promise<Animal[]> {
    return await this.animalRepository.find();
  }

  async findOne(id: number): Promise<Animal> {
    const animal = await this.animalRepository.findOneBy({ idAnimal: id });
    if (!animal) {
      throw new NotFoundException(`El animal con ID ${id} no fue encontrado`);
    }
    return animal;
  }

  async update(id: number, updateAnimalDto: UpdateAnimalDto): Promise<Animal> {
    const animal = await this.findOne(id);
    this.animalRepository.merge(animal, updateAnimalDto);
    return await this.animalRepository.save(animal);
  }

  async remove(id: number): Promise<void> {
    const animal = await this.findOne(id);
    await this.animalRepository.remove(animal);
  }

  async findByPotrero(potreroId: number): Promise<Animal[]> {
    return await this.animalRepository.findBy({ potreroId });
  }

  async findByRaza(razaId: number): Promise<Animal[]> {
    return await this.animalRepository.findBy({ razaId });
  }
}